<?php
require_once __DIR__ . '/../bootstrap.php'; require_method('POST'); $d=request_json();
$bid=(int)($d['business_id']??0); $sid=(int)($d['staff_id']??0)?:null; $feedback=trim((string)($d['feedback']??'')); $language=strtolower(trim((string)($d['language']??'english'))); $tags=is_array($d['tags']??null)?array_values(array_filter(array_map(fn($v)=>trim((string)$v),$d['tags']))):[];
if(!$bid||($feedback===''&&!$tags))json_response(['ok'=>false,'error'=>'Please add a short note or select at least one genuine highlight'],422);
$st=db()->prepare("SELECT b.*,s.review_min_words,s.review_max_words,s.require_staff_selection FROM businesses b JOIN business_settings s ON s.business_id=b.id WHERE b.id=? AND b.status='active'");$st->execute([$bid]);$b=$st->fetch();if(!$b)json_response(['ok'=>false,'error'=>'Business not found'],404);
if((int)$b['require_staff_selection']===1&&!$sid)json_response(['ok'=>false,'error'=>'Please select the staff member who assisted you'],422);
if($sid){$ss=db()->prepare('SELECT id,name FROM staff WHERE id=? AND business_id=? AND is_active=1');$ss->execute([$sid,$bid]);$staff=$ss->fetch();if(!$staff)json_response(['ok'=>false,'error'=>'Invalid staff selection'],422);} else $staff=null;

$kwq=db()->prepare('SELECT keyword,category,priority FROM keywords WHERE business_id=? AND is_active=1 ORDER BY priority DESC,id DESC LIMIT 120');$kwq->execute([$bid]);$keywords=$kwq->fetchAll();
$kwText=implode(', ',array_map(fn($x)=>$x['keyword'],array_slice($keywords,0,18)));
$identityText=strtolower(($b['category']??'').' '.$kwText.' '.($b['description']??''));
function infer_type($s){
  $map=[
    'Dermatology / Skin Clinic'=>['dermat','skin clinic','laser','acne','hair removal','clinic','derma','cosmetology'],
    'Salon / Beauty'=>['salon','hair','makeup','beauty','nail','spa','keratin'],
    'Bridal / Fashion'=>['bridal','lehenga','saree','ethnic','fashion','dress','wedding'],
    'Restaurant / Cafe'=>['restaurant','cafe','food','dining','menu','bakery'],
    'Retail Store'=>['store','retail','shop','shopping','collection','product'],
    'Healthcare'=>['doctor','hospital','dental','physio','health'],
    'Education / Academy'=>['academy','course','training','institute','school'],
    'Local Service Business'=>[]
  ];
  foreach($map as $type=>$needles){foreach($needles as $n){if(str_contains($s,$n))return $type;}}
  return 'Local Service Business';
}
$businessType=infer_type($identityText);
$corpus=strtolower($feedback.' '.implode(' ',$tags));$scored=[];
foreach($keywords as $k){$words=preg_split('/\s+/',strtolower($k['keyword']));$score=((int)$k['priority'])/100;foreach($words as $w){if(strlen($w)>3&&str_contains($corpus,$w))$score+=3;}if(str_contains(strtolower($k['keyword']),strtolower((string)$b['city'])))$score+=.15;if($score>.5)$scored[]=[$k['keyword'],$score];}
usort($scored,fn($a,$b)=>$b[1]<=>$a[1]);$top=array_slice($scored,0,min(8,count($scored)));$candidate=$top?$top[array_rand($top)][0]:null;
$recentQ=db()->prepare('SELECT generated_review FROM review_drafts WHERE business_id=? ORDER BY id DESC LIMIT 12');$recentQ->execute([$bid]);$recent=$recentQ->fetchAll(PDO::FETCH_COLUMN);$recentOpenings=array_values(array_filter(array_map(function($x){$p=preg_split('/(?<=[.!?])\s+/',trim((string)$x),2);return $p[0]??'';},$recent)));
$min=max(45,(int)$b['review_min_words']);$max=min(110,max($min,(int)$b['review_max_words']));$nonce=bin2hex(random_bytes(4));
$positive=implode(', ',$tags);
$prompt="You are GrowReview's customer-review writing assistant.\nBusiness: {$b['name']}\nDetected business type: {$businessType}\nOfficial category: ".($b['category']?:'not specified')."\nCity: ".($b['city']?:'not specified')."\nBusiness description: ".($b['description']?:'not provided')."\nHigh-priority local/business keyword context: {$kwText}\nCustomer's own note: {$feedback}\nCustomer-selected genuine highlights: {$positive}\nLanguage: {$language}\n\nWrite ONE natural review between {$min} and {$max} words. Make it sound like a real customer, not an SEO writer. If the customer selected positive highlights, express clear appreciation for those exact points with warm, specific but non-invented praise. Adapt vocabulary to the detected business type. For a clinic, talk naturally about consultation/service/staff only when supported; for a salon, collection/styling/service only when supported; for retail/fashion, collection/variety/staff only when supported. Never invent treatment results, purchases, prices, medical claims, names, facilities, products, or experiences the customer did not provide. Use at most ONE relevant keyword phrase naturally, only if it fits. Do not force the city name. Avoid generic filler such as 'formed my own impression', 'straightforward experience', 'worth sharing', 'without feeling rushed' unless the customer actually said that. Avoid repeating these recent openings: ".implode(' | ',$recentOpenings).". Return review text only. Variation id: {$nonce}.";

function call_gemini($prompt,$api,$model){$url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';$payload=['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['temperature'=>1.1,'topP'=>.96,'maxOutputTokens'=>260]];$ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-goog-api-key: '.$api],CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_TIMEOUT=>20]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if(!$raw||$code<200||$code>=300)return ''; $j=json_decode($raw,true);return trim((string)($j['candidates'][0]['content']['parts'][0]['text']??'')," \t\n\r\0\x0B\"");}
function sim($a,$b){$na=preg_replace('/[^a-z0-9 ]+/',' ',strtolower($a));$nb=preg_replace('/[^a-z0-9 ]+/',' ',strtolower($b));$wa=array_unique(array_filter(explode(' ',$na),fn($x)=>strlen($x)>3));$wb=array_unique(array_filter(explode(' ',$nb),fn($x)=>strlen($x)>3));if(!$wa||!$wb)return 0;$inter=count(array_intersect($wa,$wb));return $inter/max(1,min(count($wa),count($wb)));}
$review='';$provider='fallback';$api=envv('GEMINI_API_KEY','');$model=envv('GEMINI_MODEL','gemini-2.5-flash');
if($api){for($attempt=0;$attempt<3;$attempt++){ $r=call_gemini($prompt."\nAttempt: ".($attempt+1),$api,$model); if($r==='')continue; $wc=count(preg_split('/\s+/',trim($r))); if($wc<$min||$wc>$max)continue; $tooSimilar=false;foreach($recent as $old){if(sim($r,$old)>.72){$tooSimilar=true;break;}}if(!$tooSimilar){$review=$r;$provider='gemini';break;}}}
if($review===''){
  $subjects=$tags?:[$feedback];$subject=implode(', ',$subjects);
  $openings=["I had a really positive experience at {$b['name']}.","My visit to {$b['name']} was a very good one.","I recently visited {$b['name']} and came away genuinely happy with the experience.","{$b['name']} made a very good impression on me during my recent visit.","I enjoyed my recent experience with {$b['name']}."];
  $typeLines=[
    'Dermatology / Skin Clinic'=>["What I appreciated most was {$subject}.","The part that stood out to me was {$subject}, and it made the visit feel well handled."],
    'Salon / Beauty'=>["I especially liked {$subject}; it made the overall salon experience much better.","What stood out most was {$subject}."],
    'Bridal / Fashion'=>["I especially liked {$subject}, which made the shopping experience feel enjoyable.","The strongest part of the visit for me was {$subject}."],
    'Retail Store'=>["I particularly liked {$subject}, and it made the visit more enjoyable.","The main highlight for me was {$subject}."],
    'Local Service Business'=>["I particularly appreciated {$subject}.","The main thing that stood out to me was {$subject}."]
  ];
  $lines=$typeLines[$businessType]??$typeLines['Local Service Business'];
  $closings=["The experience felt customer-friendly and left me with a very positive impression.","Overall, I was very happy with the way the visit went and would be comfortable recommending the business based on my experience.","Overall, the experience was pleasant and the points I selected are exactly what I appreciated most.","It was a good experience overall, and I genuinely appreciated how those parts of the visit were handled."];
  $review=$openings[array_rand($openings)].' '.$lines[array_rand($lines)].' '.$closings[array_rand($closings)];
  $fillers=["The overall interaction felt welcoming and well organised.","I appreciated the attention given during the visit.","The experience felt comfortable and easy from a customer's point of view.","The way everything was handled added to the positive experience."];
  while(count(preg_split('/\s+/',trim($review)))<$min){$review.=' '.$fillers[array_rand($fillers)];}
  $words=preg_split('/\s+/',trim($review));if(count($words)>$max)$review=implode(' ',array_slice($words,0,$max)).'.';
}
$wc=count(preg_split('/\s+/',trim($review)));
$ins=db()->prepare('INSERT INTO review_drafts(business_id,staff_id,customer_feedback,selected_tags_json,language,keyword_used,generated_review,word_count,ai_provider) VALUES(?,?,?,?,?,?,?,?,?)');
$ins->execute([$bid,$sid,$feedback,$tags?json_encode($tags,JSON_UNESCAPED_UNICODE):null,$language,$candidate,$review,$wc,$provider]);
db()->prepare("INSERT INTO events(business_id,staff_id,event_type,metadata_json) VALUES(?,?, 'review_generated', ?)")->execute([$bid,$sid,json_encode(['review_id'=>(int)db()->lastInsertId(),'business_type'=>$businessType],JSON_UNESCAPED_UNICODE)]);
json_response(['ok'=>true,'review'=>$review,'word_count'=>$wc,'keyword_used'=>$candidate,'provider'=>$provider,'business_type'=>$businessType]);
