-- database/schema.sql
--
-- One-time setup: run this entire file once against the Hostinger MySQL
-- database (hPanel > Databases > phpMyAdmin > Import, or the phpMyAdmin SQL
-- tab). It creates every table the CMS needs and seeds them with the site's
-- current live copy, so the public pages render identically immediately
-- after this runs. Re-running it will fail on the CREATE TABLEs (tables
-- already exist) -- it is not idempotent and is not meant to be re-applied.

CREATE TABLE admin_users (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Singleton scalar fields, scoped per public page. No fallback/merge logic --
-- every row a page needs is seeded explicitly below.
CREATE TABLE site_settings (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  page VARCHAR(32) NOT NULL,
  section VARCHAR(32) NOT NULL,
  field_key VARCHAR(64) NOT NULL,
  field_value MEDIUMTEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_field (page, section, field_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE nav_links (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  link_key VARCHAR(32) NOT NULL,
  label VARCHAR(64) NOT NULL,
  href VARCHAR(191) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE socials (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  label VARCHAR(64) NOT NULL,
  href VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stats (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  value INT NOT NULL,
  label VARCHAR(64) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE work_projects (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(191) NOT NULL UNIQUE,
  title VARCHAR(160) NOT NULL,
  tag VARCHAR(64) NOT NULL,
  year VARCHAR(8) NOT NULL,
  card_image_path VARCHAR(255) NULL,
  card_placeholder_text VARCHAR(160) NOT NULL,
  lead TEXT NOT NULL,
  hero_image_path VARCHAR(255) NULL,
  hero_placeholder_text VARCHAR(160) NOT NULL,
  synopsis_heading VARCHAR(120) NOT NULL DEFAULT 'Synopsis',
  synopsis_paragraphs JSON NOT NULL,
  credits JSON NOT NULL,
  tags JSON NOT NULL,
  stills_eyebrow VARCHAR(64) NOT NULL DEFAULT 'Stills',
  stills_heading VARCHAR(120) NOT NULL DEFAULT 'From the field.',
  stills JSON NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  featured_sort_order INT NOT NULL DEFAULT 0,
  archive_sort_order INT NOT NULL DEFAULT 0,
  status ENUM('published','draft') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_featured (status, is_featured, featured_sort_order),
  INDEX idx_archive (status, archive_sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE journal_entries (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(191) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  kicker VARCHAR(64) NOT NULL,
  card_body TEXT NOT NULL,
  published_on DATE NOT NULL,
  subtitle VARCHAR(255) NOT NULL,
  hero_image_path VARCHAR(255) NULL,
  hero_placeholder_text VARCHAR(160) NOT NULL,
  intro_paragraphs JSON NOT NULL,
  inline_image_path VARCHAR(255) NULL,
  inline_placeholder_text VARCHAR(160) NOT NULL,
  subheading VARCHAR(160) NOT NULL,
  subheading_paragraphs JSON NOT NULL,
  tags JSON NOT NULL,
  archive_sort_order INT NOT NULL DEFAULT 0,
  featured_sort_order INT NULL,
  status ENUM('published','draft') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_featured (status, featured_sort_order),
  INDEX idx_archive (status, archive_sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_submissions (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(191) NOT NULL,
  details TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_unread (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed data -- the site's current live copy, verbatim, so the
-- public pages render identically immediately after this runs.
-- ============================================================

-- Default admin login: username "admin", password "ChangeMe123!"
-- Log in at /admin/login.php and change this immediately via
-- /admin/change-password.php -- see README.md.

INSERT INTO admin_users (username, password_hash) VALUES
  ('admin', '$2y$10$QvD3Mkz7hLJs0HmbLOEpgewORYBPBPuyVhJcOD6wN55lcPj.NCUkm');

INSERT INTO site_settings (page, section, field_key, field_value) VALUES
  ('home', 'nav', 'brand', 'AASHISH RAI'),
  ('home', 'nav', 'brandHref', '#hero'),
  ('home', 'nav', 'navCtaLabel', 'Get in touch'),
  ('home', 'nav', 'navCtaHref', '#contact'),
  ('home', 'hero', 'eyebrow', 'Documentary Director — Creative Producer'),
  ('home', 'hero', 'titleLine1', 'Aashish'),
  ('home', 'hero', 'titleLine2', 'Rai'),
  ('home', 'hero', 'subtitle', 'Stories built frame by frame — documentary, accessibility, and the quiet drama of everyday life, produced and directed end to end.'),
  ('home', 'hero', 'ctaPrimaryLabel', 'See the work'),
  ('home', 'hero', 'ctaPrimaryHref', '#work'),
  ('home', 'hero', 'ctaSecondaryLabel', 'Start a project'),
  ('home', 'hero', 'ctaSecondaryHref', '#contact'),
  ('home', 'hero', 'scrollLabel', 'Scroll'),
  ('home', 'hero', 'recLabel', 'REC 00:00:12:04'),
  ('home', 'hero', 'portraitPlaceholder', 'Portrait — Aashish Rai'),
  ('home', 'hero', 'portraitImagePath', ''),
  ('home', 'about', 'eyebrow', 'About'),
  ('home', 'about', 'heading', 'Directing with care, producing with rigor.'),
  ('home', 'about', 'paragraph1', 'Aashish Rai makes documentary films and leads the productions behind them — moving between the camera and the call sheet without losing sight of either. The work spans accessibility campaigns, character-driven nonfiction, podcasts, and brand films, all built on the same instinct: point the camera at what''s actually true.'),
  ('home', 'about', 'paragraph2', 'Recent work has screened at regional festivals and shipped for clients across health, education, and media.'),
  ('home', 'about', 'portraitPlaceholder', 'Aashish Rai — on set'),
  ('home', 'about', 'portraitImagePath', ''),
  ('home', 'services_intro', 'eyebrow', 'What I Do'),
  ('home', 'services_intro', 'heading', 'Direction and production, from first frame to final cut.'),
  ('home', 'work_intro', 'eyebrow', 'Selected Work'),
  ('home', 'work_intro', 'heading', 'A decade of stories, told frame by frame.'),
  ('home', 'work_intro', 'description', 'Documentary features, accessibility campaigns, podcasts, and brand films — six recent works.'),
  ('home', 'work_intro', 'viewAllLabel', 'View all work'),
  ('home', 'work_intro', 'viewAllHref', 'work-archive.php'),
  ('home', 'journal_intro', 'eyebrow', 'Journal'),
  ('home', 'journal_intro', 'heading', 'Notes from the edit room.'),
  ('home', 'journal_intro', 'viewAllLabel', 'View all entries'),
  ('home', 'journal_intro', 'viewAllHref', 'journal-archive.php'),
  ('home', 'journal_intro', 'section_enabled', '1'),
  ('home', 'contact', 'eyebrow', 'Contact'),
  ('home', 'contact', 'heading', 'Let''s make something honest.'),
  ('home', 'contact', 'email', 'hello@aashishrai.com'),
  ('home', 'contact', 'formNameLabel', 'Name'),
  ('home', 'contact', 'formNamePlaceholder', 'Your name'),
  ('home', 'contact', 'formEmailLabel', 'Email'),
  ('home', 'contact', 'formEmailPlaceholder', 'you@studio.com'),
  ('home', 'contact', 'formDetailsLabel', 'Project details'),
  ('home', 'contact', 'formDetailsPlaceholder', 'What are you making?'),
  ('home', 'contact', 'submitLabel', 'Send inquiry'),
  ('home', 'footer', 'copyright', '© 2026 Aashish Rai'),
  ('home', 'footer', 'backToTopLabel', 'Back to top ↑'),
  ('work', 'nav', 'brand', 'AASHISH RAI'),
  ('work', 'nav', 'brandHref', 'index.php#hero'),
  ('work', 'nav', 'homeHref', 'index.php'),
  ('work', 'nav', 'homeLabel', 'All work'),
  ('work', 'nav', 'navCtaLabel', 'Get in touch'),
  ('work', 'nav', 'navCtaHref', 'index.php#contact'),
  ('work', 'hero', 'eyebrow', 'Archive'),
  ('work', 'hero', 'heading', 'Every project, in order.'),
  ('work', 'hero', 'description', 'Documentary features, accessibility campaigns, podcasts, brand films, and photography — the full record, 2019 to present.'),
  ('work', 'footer', 'copyright', '© 2026 Aashish Rai'),
  ('work', 'footer', 'backHomeLabel', 'Back to home ↑'),
  ('journal', 'nav', 'brand', 'AASHISH RAI'),
  ('journal', 'nav', 'brandHref', 'index.php#hero'),
  ('journal', 'nav', 'homeHref', 'index.php'),
  ('journal', 'nav', 'homeLabel', 'Home'),
  ('journal', 'nav', 'archiveHref', 'journal-archive.php'),
  ('journal', 'nav', 'archiveLabel', 'All entries'),
  ('journal', 'nav', 'navCtaLabel', 'Get in touch'),
  ('journal', 'nav', 'navCtaHref', 'index.php#contact'),
  ('journal', 'hero', 'eyebrow', 'Journal'),
  ('journal', 'hero', 'heading', 'Notes from the edit room.'),
  ('journal', 'hero', 'description', 'Writing on craft, accessibility, and the small decisions behind the work.'),
  ('journal', 'footer', 'copyright', '© 2026 Aashish Rai'),
  ('journal', 'footer', 'backHomeLabel', 'Back to home ↑');

INSERT INTO nav_links (link_key, label, href, sort_order, enabled) VALUES
  ('about', 'About', '#about', 1, 1),
  ('services', 'Services', '#services', 2, 1),
  ('work', 'Work', '#work', 3, 1),
  ('journal', 'Journal', '#journal', 4, 1);

INSERT INTO socials (label, href, sort_order) VALUES
  ('Instagram', '#', 1),
  ('Vimeo', '#', 2),
  ('LinkedIn', '#', 3);

INSERT INTO services (title, description, sort_order) VALUES
  ('Documentary Direction', 'Long-form nonfiction from concept through final color — festival features and character-driven shorts.', 1),
  ('Creative Production', 'Budgets, crews, permits, schedules — the structure that lets a story get made on time and intact.', 2),
  ('Accessibility Consulting', 'Captioning, audio description, and inclusive production practice built into the process, not bolted on after.', 3),
  ('Podcast Direction & Production', 'Series development, sound design, and episodic direction for narrative and interview formats.', 4),
  ('Digital & Brand Campaigns', 'Short-form and social-native films built around a brand''s real story, not a template.', 5),
  ('Photography & Videography', 'Stills and motion on the same set — a single eye across both mediums.', 6);

INSERT INTO stats (value, label, sort_order) VALUES
  (10, 'Years directing', 1),
  (40, 'Projects produced', 2),
  (15, 'Festival selections', 3);

INSERT INTO work_projects (
  slug, title, tag, year, card_image_path, card_placeholder_text, lead,
  hero_image_path, hero_placeholder_text, synopsis_heading, synopsis_paragraphs,
  credits, tags, stills_eyebrow, stills_heading, stills,
  is_featured, featured_sort_order, archive_sort_order
) VALUES
  ('in-plain-sight', 'In Plain Sight', 'Documentary', '2025', NULL, 'In Plain Sight — still', 'A feature documentary following three accessibility advocates rebuilding public space for their own communities — filmed over eighteen months with audio description built into the shoot, not added after.', NULL, 'In Plain Sight — key frame', 'Synopsis', '[{"text":"Shot across four cities, In Plain Sight follows Meera, Josiah, and Tomas as they push their local governments to rebuild sidewalks, transit stops, and public buildings around the way they actually move through the world — not the way planners assumed they would."},{"text":"The film was directed with an accessibility consultant embedded from the first location scout, and every frame was cut with an eye toward what an audio-described version would need to carry."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Producer","value":"Aashish Rai"},{"label":"Runtime","value":"42 min"},{"label":"Format","value":"Documentary Feature"},{"label":"Year","value":"2025"},{"label":"Selection","value":"Riverside Doc Festival"}]', '[{"label":"Documentary","variant":"accent"},{"label":"2025","variant":"neutral"},{"label":"42 min","variant":"outline"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 1, 100),
  ('signal-silence', 'Signal & Silence', 'Podcast', '2024', NULL, 'Signal & Silence — still', 'More on Signal & Silence is coming soon — a Podcast project from 2024.', NULL, 'Signal & Silence — key frame', 'Synopsis', '[{"text":"Full write-up for Signal & Silence is coming soon — check back for more on this Podcast project from 2024."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Podcast"},{"label":"Year","value":"2024"}]', '[{"label":"Podcast","variant":"accent"},{"label":"2024","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 2, 90),
  ('the-unseen-frame', 'The Unseen Frame', 'Accessibility', '2024', NULL, 'The Unseen Frame — still', 'More on The Unseen Frame is coming soon — an Accessibility project from 2024.', NULL, 'The Unseen Frame — key frame', 'Synopsis', '[{"text":"Full write-up for The Unseen Frame is coming soon — check back for more on this Accessibility project from 2024."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Accessibility"},{"label":"Year","value":"2024"}]', '[{"label":"Accessibility","variant":"accent"},{"label":"2024","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 3, 80),
  ('currents', 'Currents', 'Digital Campaign', '2023', NULL, 'Currents — still', 'More on Currents is coming soon — a Digital Campaign project from 2023.', NULL, 'Currents — key frame', 'Synopsis', '[{"text":"Full write-up for Currents is coming soon — check back for more on this Digital Campaign project from 2023."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Digital Campaign"},{"label":"Year","value":"2023"}]', '[{"label":"Digital Campaign","variant":"accent"},{"label":"2023","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 4, 70),
  ('still-moving', 'Still, Moving', 'Photography', '2023', NULL, 'Still, Moving — series', 'More on Still, Moving is coming soon — a Photography project from 2023.', NULL, 'Still, Moving — key frame', 'Synopsis', '[{"text":"Full write-up for Still, Moving is coming soon — check back for more on this Photography project from 2023."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Photography"},{"label":"Year","value":"2023"}]', '[{"label":"Photography","variant":"accent"},{"label":"2023","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 5, 60),
  ('ordinary-light', 'Ordinary Light', 'Documentary Short', '2022', NULL, 'Ordinary Light — still', 'More on Ordinary Light is coming soon — a Documentary Short project from 2022.', NULL, 'Ordinary Light — key frame', 'Synopsis', '[{"text":"Full write-up for Ordinary Light is coming soon — check back for more on this Documentary Short project from 2022."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Documentary Short"},{"label":"Year","value":"2022"}]', '[{"label":"Documentary Short","variant":"accent"},{"label":"2022","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 1, 6, 50),
  ('held-ground', 'Held Ground', 'Documentary', '2021', NULL, 'Held Ground — still', 'More on Held Ground is coming soon — a Documentary project from 2021.', NULL, 'Held Ground — key frame', 'Synopsis', '[{"text":"Full write-up for Held Ground is coming soon — check back for more on this Documentary project from 2021."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Documentary"},{"label":"Year","value":"2021"}]', '[{"label":"Documentary","variant":"accent"},{"label":"2021","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 0, 0, 40),
  ('waiting-room', 'Waiting Room', 'Podcast', '2021', NULL, 'Waiting Room — podcast', 'More on Waiting Room is coming soon — a Podcast project from 2021.', NULL, 'Waiting Room — key frame', 'Synopsis', '[{"text":"Full write-up for Waiting Room is coming soon — check back for more on this Podcast project from 2021."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Podcast"},{"label":"Year","value":"2021"}]', '[{"label":"Podcast","variant":"accent"},{"label":"2021","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 0, 0, 30),
  ('threshold', 'Threshold', 'Digital Campaign', '2020', NULL, 'Threshold — campaign', 'More on Threshold is coming soon — a Digital Campaign project from 2020.', NULL, 'Threshold — key frame', 'Synopsis', '[{"text":"Full write-up for Threshold is coming soon — check back for more on this Digital Campaign project from 2020."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Digital Campaign"},{"label":"Year","value":"2020"}]', '[{"label":"Digital Campaign","variant":"accent"},{"label":"2020","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 0, 0, 20),
  ('field-notes', 'Field Notes', 'Photography', '2019', NULL, 'Field Notes — series', 'More on Field Notes is coming soon — a Photography project from 2019.', NULL, 'Field Notes — key frame', 'Synopsis', '[{"text":"Full write-up for Field Notes is coming soon — check back for more on this Photography project from 2019."}]', '[{"label":"Director","value":"Aashish Rai"},{"label":"Format","value":"Photography"},{"label":"Year","value":"2019"}]', '[{"label":"Photography","variant":"accent"},{"label":"2019","variant":"neutral"}]', 'Stills', 'From the field.', '[{"image_path":null,"placeholder":"Still 1"},{"image_path":null,"placeholder":"Still 2"},{"image_path":null,"placeholder":"Still 3"}]', 0, 0, 10);

INSERT INTO journal_entries (
  slug, title, kicker, card_body, published_on, subtitle,
  hero_image_path, hero_placeholder_text, intro_paragraphs,
  inline_image_path, inline_placeholder_text, subheading, subheading_paragraphs,
  tags, archive_sort_order, featured_sort_order
) VALUES
  ('on-filming-what-cant-be-seen', 'On filming what can''t be seen', 'Accessibility', 'Notes on audio description as a directing choice, not a compliance checkbox.', '2026-06-01', 'Notes on audio description as a directing choice, made on set, not fixed in post.', NULL, 'Journal entry — key frame', '[{"text":"Most audio description gets written after the film is locked — a narrator reading the screen back to a viewer who can''t see it. I''ve come to think that''s backwards. If a scene only works because of what it looks like, and you can''t say what it looks like in the time the scene gives you, the scene has a problem the edit should have caught."},{"text":"On In Plain Sight, our accessibility consultant sat in on blocking rehearsals, not just the final color pass. If an action couldn''t be described in the natural pause of the dialogue around it, we restaged it. That discipline made the film better for every viewer, not just the ones using the described track."}]', NULL, 'On set — accessibility consultant reviewing blocking', 'Three things I now ask on every shoot', '[{"text":"Is there a natural pause here for a description to live in. Does this shot tell you something a sighted viewer gets for free. And if I cut this the way I want to, does the meaning survive without the picture."},{"text":"None of this slows a shoot down much. It mostly just means the accessibility consultant is in the room from day one, not the finishing suite."}]', '[{"label":"Accessibility","variant":"accent"},{"label":"Jun 2026","variant":"neutral"}]', 60, 1),
  ('the-producers-real-job', 'The producer''s real job', 'Craft', 'It''s rarely the budget. It''s protecting the two hours where the scene actually happens.', '2026-04-01', 'It''s rarely the budget. It''s protecting the two hours where the scene actually happens.', NULL, 'The producer''s real job — key frame', '[{"text":"It''s rarely the budget. It''s protecting the two hours where the scene actually happens."},{"text":"The full entry is coming soon — check back for the complete piece."}]', NULL, 'The producer''s real job — inline image', 'More soon', '[{"text":"This entry is still being written — check back soon for the complete piece."}]', '[{"label":"Craft","variant":"accent"},{"label":"Apr 2026","variant":"neutral"}]', 50, 2),
  ('sound-before-picture', 'Sound before picture', 'Podcast', 'Why every video project on my slate starts with a conversation, recorded and unscripted.', '2026-02-01', 'Why every video project on my slate starts with a conversation, recorded and unscripted.', NULL, 'Sound before picture — key frame', '[{"text":"Why every video project on my slate starts with a conversation, recorded and unscripted."},{"text":"The full entry is coming soon — check back for the complete piece."}]', NULL, 'Sound before picture — inline image', 'More soon', '[{"text":"This entry is still being written — check back soon for the complete piece."}]', '[{"label":"Podcast","variant":"accent"},{"label":"Feb 2026","variant":"neutral"}]', 40, 3),
  ('what-eighteen-months-in-the-field-teaches-you', 'What eighteen months in the field teaches you', 'Documentary', 'On patience, trust, and the footage that never makes the cut but changes everything after.', '2025-12-01', 'On patience, trust, and the footage that never makes the cut but changes everything after.', NULL, 'What eighteen months in the field teaches you — key frame', '[{"text":"On patience, trust, and the footage that never makes the cut but changes everything after."},{"text":"The full entry is coming soon — check back for the complete piece."}]', NULL, 'What eighteen months in the field teaches you — inline image', 'More soon', '[{"text":"This entry is still being written — check back soon for the complete piece."}]', '[{"label":"Documentary","variant":"accent"},{"label":"Dec 2025","variant":"neutral"}]', 30, NULL),
  ('cutting-for-the-ear-not-just-the-eye', 'Cutting for the ear, not just the eye', 'Craft', 'A working note on pacing picture around sound design instead of the other way around.', '2025-09-01', 'A working note on pacing picture around sound design instead of the other way around.', NULL, 'Cutting for the ear, not just the eye — key frame', '[{"text":"A working note on pacing picture around sound design instead of the other way around."},{"text":"The full entry is coming soon — check back for the complete piece."}]', NULL, 'Cutting for the ear, not just the eye — inline image', 'More soon', '[{"text":"This entry is still being written — check back soon for the complete piece."}]', '[{"label":"Craft","variant":"accent"},{"label":"Sep 2025","variant":"neutral"}]', 20, NULL),
  ('the-permit-is-the-film', 'The permit is the film', 'Production', 'Why the unglamorous logistics of access are often the real creative work on a documentary set.', '2025-06-01', 'Why the unglamorous logistics of access are often the real creative work on a documentary set.', NULL, 'The permit is the film — key frame', '[{"text":"Why the unglamorous logistics of access are often the real creative work on a documentary set."},{"text":"The full entry is coming soon — check back for the complete piece."}]', NULL, 'The permit is the film — inline image', 'More soon', '[{"text":"This entry is still being written — check back soon for the complete piece."}]', '[{"label":"Production","variant":"accent"},{"label":"Jun 2025","variant":"neutral"}]', 10, NULL);
