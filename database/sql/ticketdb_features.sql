USE ticketdb;

DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS event_invites;
DROP TABLE IF EXISTS friends;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS event_artists;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS venues;
DROP TABLE IF EXISTS artists;

CREATE TABLE artists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  genre VARCHAR(100),
  image_url VARCHAR(300),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_artists_name ON artists(name);

CREATE TABLE venues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  city VARCHAR(100),
  state VARCHAR(50),
  country VARCHAR(50),
  address VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_venues_name ON venues(name);
CREATE INDEX idx_venues_city ON venues(city);

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  event_date DATETIME NOT NULL,
  venue_id INT,
  status VARCHAR(20) DEFAULT 'AVAILABLE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (venue_id) REFERENCES venues(id)
);

CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_title ON events(title);

CREATE TABLE event_artists (
  event_id INT NOT NULL,
  artist_id INT NOT NULL,
  PRIMARY KEY (event_id, artist_id),
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (artist_id) REFERENCES artists(id)
);

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  rating INT NOT NULL,
  review_text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL,
  UNIQUE (user_id, event_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE INDEX idx_reviews_event ON reviews(event_id);
CREATE INDEX idx_reviews_user ON reviews(user_id);

CREATE TABLE friends (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  friend_user_id INT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, friend_user_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (friend_user_id) REFERENCES users(id)
);

CREATE TABLE event_invites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  inviter_user_id INT NOT NULL,
  invitee_user_id INT NULL,
  invite_token VARCHAR(100) NOT NULL UNIQUE,
  status VARCHAR(20) NOT NULL DEFAULT 'SENT',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NULL,
  FOREIGN KEY (event_id) REFERENCES events(id),
  FOREIGN KEY (inviter_user_id) REFERENCES users(id),
  FOREIGN KEY (invitee_user_id) REFERENCES users(id)
);

CREATE INDEX idx_invites_event ON event_invites(event_id);
CREATE INDEX idx_invites_inviter ON event_invites(inviter_user_id);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  channel VARCHAR(10) NOT NULL,
  title VARCHAR(120) NOT NULL,
  message VARCHAR(500) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_status ON notifications(status);

CREATE TABLE bookings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	event_id INT NOT NULL,
	qty INT NOT NULL DEFAULT 1,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES users(id),
	FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_event ON bookings(event_id);

-- email/phonn columns are required for notifications
-- if they are added once, do not add them here again
-- run the commands in the terminal section below if needed
