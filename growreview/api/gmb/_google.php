<?php
function gbp_access_token(): string {
  $cid=envv('GOOGLE_CLIENT_ID','');$secret=envv('GOOGLE_CLIENT_SECRET','');$refresh=envv('GOOGLE_REFRESH_TOKEN','');
  if(!$cid||!$secret||!$refresh) throw new RuntimeException('Google OAuth is not configured in .env');
  $ch=curl_init('https://oauth2.googleapis.com/token');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['client_id'=>$cid,'client_secret'=>$secret,'refresh_token'=>$refresh,'grant_type'=>'refresh_token']),CURLOPT_TIMEOUT=>20]);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$j=json_decode((string)$raw,true);if($code<200||$code>=300||empty($j['access_token']))throw new RuntimeException('Unable to refresh Google OAuth token');return $j['access_token'];
}
function gbp_request(string $method,string $url,?array $body=null): array {
  $token=gbp_access_token();$ch=curl_init($url);$headers=['Authorization: Bearer '.$token,'Content-Type: application/json'];$opts=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>25];if($body!==null)$opts[CURLOPT_POSTFIELDS]=json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);curl_setopt_array($ch,$opts);$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$j=json_decode((string)$raw,true);if($code<200||$code>=300)throw new RuntimeException((string)($j['error']['message']??'Google Business Profile API request failed'));return is_array($j)?$j:[];
}
