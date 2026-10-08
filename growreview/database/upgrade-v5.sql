-- GrowReview CRM V5 Performance + Content upgrade
-- Safe additive migration: does not drop existing businesses, users, reviews, staff or customers.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  category VARCHAR(100) DEFAULT 'Growth',
  excerpt VARCHAR(420) DEFAULT NULL,
  content LONGTEXT DEFAULT NULL,
  cover_image TEXT DEFAULT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_status_published (status,published_at),
  INDEX idx_blog_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED DEFAULT NULL,
  author_name VARCHAR(160) NOT NULL,
  author_role VARCHAR(160) DEFAULT NULL,
  business_name VARCHAR(180) DEFAULT NULL,
  quote TEXT NOT NULL,
  rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_testimonial_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
  INDEX idx_testimonial_public (is_published,sort_order,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gmb_services_workspace (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(220) NOT NULL,
  description TEXT DEFAULT NULL,
  price_text VARCHAR(80) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  google_synced_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_gmb_service_workspace_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  UNIQUE KEY uq_gmb_service_name (business_id,name),
  INDEX idx_gmb_service_business (business_id,is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gmb_products_workspace (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(220) NOT NULL,
  category VARCHAR(140) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  price_text VARCHAR(80) DEFAULT NULL,
  image_url TEXT DEFAULT NULL,
  cta_url TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_gmb_product_workspace_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  INDEX idx_gmb_product_business (business_id,is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gmb_assets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  asset_type ENUM('photo','cover','profile') NOT NULL DEFAULT 'photo',
  file_url TEXT NOT NULL,
  caption TEXT DEFAULT NULL,
  google_synced_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gmb_assets_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  INDEX idx_gmb_asset_business (business_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gmb_audits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  audit_type VARCHAR(80) NOT NULL DEFAULT 'local_rank',
  score INT DEFAULT NULL,
  output LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gmb_audit_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  INDEX idx_gmb_audit_business (business_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO blog_posts(title,slug,category,excerpt,content,status,published_at)
VALUES
('What a stronger Google Business Profile actually needs','stronger-google-business-profile','Local SEO','Reviews, fresh media, accurate services and consistent customer actions work better together than one isolated tactic.','Use GrowReview to turn business information into a practical profile improvement workflow. Keep profile information accurate, publish useful updates, add current photos, maintain services and make genuine customer feedback easy to share.','published',NOW()),
('The right moment to ask for a review','right-moment-to-ask-for-review','Reviews','The review request works best when it is part of the customer journey, not a pressure script.','Place the QR or review action at a natural moment after service. Let customers describe their real experience, keep staff attribution separate from rating pressure, and measure the full funnel from scan to Google open.','published',NOW()),
('Measure staff contribution beyond review counts','measure-staff-contribution','Analytics','Track scans, drafts, Google opens and CRM forms to understand which customer interactions convert.','Raw review count is only one metric. GrowReview combines page opens, review drafts, Google review opens, forms and staff attribution to give managers a more useful operational view.','published',NOW())
ON DUPLICATE KEY UPDATE title=VALUES(title),excerpt=VALUES(excerpt),content=VALUES(content),status='published';
