
USE newspaper_db;


-- WEBSITE ADMIN

CREATE TABLE WEBSITE_ADMIN (
    Admin_ID INT PRIMARY KEY,
    Phone VARCHAR(20),
    Email VARCHAR(100),
    Name VARCHAR(100),
    Status VARCHAR(30)
);


-- REPORTER

CREATE TABLE REPORTER (
    Staff_ID INT PRIMARY KEY,
    Email VARCHAR(100),
    Name VARCHAR(100),
    Status VARCHAR(30),
    Specialization VARCHAR(100),
    Joining_Date DATE
);


-- EDITOR

CREATE TABLE EDITOR (
    Staff_ID INT PRIMARY KEY,
    Email VARCHAR(100),
    Name VARCHAR(100),
    Status VARCHAR(30),
    Specialization VARCHAR(100),
    Joining_Date DATE
);


-- CATEGORY

CREATE TABLE CATEGORY (
    Category_ID INT PRIMARY KEY,
    Category_Name VARCHAR(100) NOT NULL
);


-- ADVERTISEMENT

CREATE TABLE ADVERTISEMENT (
    Advertisement_ID INT PRIMARY KEY,
    Brand VARCHAR(100),
    Duration INT,
    Status VARCHAR(30),
    Admin_ID INT,

    FOREIGN KEY (Admin_ID)
        REFERENCES WEBSITE_ADMIN(Admin_ID)
);


-- ARTICLE

CREATE TABLE ARTICLE (
    Article_ID INT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Content TEXT,
    Created_At DATETIME,
    Updated_At DATETIME,
    Status VARCHAR(30),
    Reviewed_At DATETIME,
    Published_At DATETIME,
    Editors_Feedback TEXT,

    Reporter_ID INT,
    Editor_ID INT,
    Category_ID INT,

    FOREIGN KEY (Reporter_ID)
        REFERENCES REPORTER(Staff_ID),

    FOREIGN KEY (Editor_ID)
        REFERENCES EDITOR(Staff_ID),

    FOREIGN KEY (Category_ID)
        REFERENCES CATEGORY(Category_ID)
);


-- TAG

CREATE TABLE TAG (
    Tag_ID INT PRIMARY KEY,
    Tag_Name VARCHAR(100) NOT NULL
);


-- ARTICLE_TAG

CREATE TABLE ARTICLE_TAG (
    Article_ID INT,
    Tag_ID INT,

    PRIMARY KEY (Article_ID, Tag_ID),

    FOREIGN KEY (Article_ID)
        REFERENCES ARTICLE(Article_ID),

    FOREIGN KEY (Tag_ID)
        REFERENCES TAG(Tag_ID)
);


-- REGISTERED READER

CREATE TABLE REGISTERED_READER (
    Reader_ID INT PRIMARY KEY,
    Email VARCHAR(100),
    Name VARCHAR(100),
    Phone_No VARCHAR(20),
    Password VARCHAR(255),
    Status VARCHAR(30)
);


-- NON-REGISTERED READER

CREATE TABLE NON_REGISTERED_READER (
    Reader_ID INT PRIMARY KEY,
    Email VARCHAR(100),
    Name VARCHAR(100),
    Phone_No VARCHAR(20),
    Password VARCHAR(255),
    Status VARCHAR(30)
);


-- SUBSCRIPTION

CREATE TABLE SUBSCRIPTION (
    Subscription_ID INT PRIMARY KEY,
    Name VARCHAR(100),
    Code VARCHAR(50),
    Frequency VARCHAR(50),
    Status VARCHAR(30),
    Time TIME,
    Expire_Date DATE
);


-- READER_SUBSCRIPTION

CREATE TABLE READER_SUBSCRIPTION (
    Reader_ID INT,
    Subscription_ID INT,
    Subscribe_Date DATE,
    Expire_Date DATE,
    Status VARCHAR(30),

    PRIMARY KEY (Reader_ID, Subscription_ID),

    FOREIGN KEY (Reader_ID)
        REFERENCES REGISTERED_READER(Reader_ID),

    FOREIGN KEY (Subscription_ID)
        REFERENCES SUBSCRIPTION(Subscription_ID)
);


-- COMMENT

CREATE TABLE COMMENT (
    Comment_ID INT PRIMARY KEY,
    Reader_ID INT,
    Article_ID INT,
    Comment_Text TEXT,
    Time_Stamp DATETIME,
    Status VARCHAR(30),

    FOREIGN KEY (Reader_ID)
        REFERENCES REGISTERED_READER(Reader_ID),

    FOREIGN KEY (Article_ID)
        REFERENCES ARTICLE(Article_ID)
);


-- BOOKMARK

CREATE TABLE BOOKMARK (
    Reader_ID INT,
    Article_ID INT,
    Bookmarked_At DATETIME,

    PRIMARY KEY (Reader_ID, Article_ID),

    FOREIGN KEY (Reader_ID)
        REFERENCES REGISTERED_READER(Reader_ID),

    FOREIGN KEY (Article_ID)
        REFERENCES ARTICLE(Article_ID)
);





-- =========================================================
-- WEBSITE ADMIN
-- =========================================================

INSERT INTO WEBSITE_ADMIN
(Admin_ID, Phone, Email, Name, Status)
VALUES
(1, '01711111111', 'admin@newspaper.com', 'Tanvir Ahmed', 'Active'),
(2, '01822222222', 'admin2@newspaper.com', 'Nusrat Jahan', 'Active');


-- =========================================================
-- REPORTER
-- =========================================================

INSERT INTO REPORTER
(Staff_ID, Email, Name, Status, Specialization, Joining_Date)
VALUES
(101, 'rahim@newspaper.com', 'Rahim Hasan', 'Active', 'Politics', '2024-01-15'),
(102, 'sadia@newspaper.com', 'Sadia Islam', 'Active', 'Technology', '2024-03-10'),
(103, 'karim@newspaper.com', 'Karim Ahmed', 'Active', 'Sports', '2023-08-20'),
(104, 'mehedi@newspaper.com', 'Mehedi Rahman', 'Active', 'Business', '2025-01-05');


-- =========================================================
-- EDITOR
-- =========================================================

INSERT INTO EDITOR
(Staff_ID, Email, Name, Status, Specialization, Joining_Date)
VALUES
(201, 'editor1@newspaper.com', 'Farhana Akter', 'Active', 'Politics', '2023-02-01'),
(202, 'editor2@newspaper.com', 'Arif Hossain', 'Active', 'Technology', '2023-06-15'),
(203, 'editor3@newspaper.com', 'Maliha Khan', 'Active', 'Sports', '2024-02-10');


-- =========================================================
-- CATEGORY
-- =========================================================

INSERT INTO CATEGORY
(Category_ID, Category_Name)
VALUES
(1, 'Politics'),
(2, 'Sports'),
(3, 'Technology'),
(4, 'Business'),
(5, 'Entertainment'),
(6, 'International'),
(7, 'Education');


-- =========================================================
-- ADVERTISEMENT
-- =========================================================

INSERT INTO ADVERTISEMENT
(Advertisement_ID, Brand, Duration, Status, Admin_ID)
VALUES
(1, 'TechWorld', 30, 'Active', 1),
(2, 'Fresh Foods BD', 15, 'Active', 1),
(3, 'Smart Electronics', 45, 'Pending', 2),
(4, 'EduCare', 30, 'Active', 2);


-- =========================================================
-- ARTICLE
-- =========================================================

INSERT INTO ARTICLE
(
    Article_ID,
    Title,
    Content,
    Created_At,
    Updated_At,
    Status,
    Reviewed_At,
    Published_At,
    Editors_Feedback,
    Reporter_ID,
    Editor_ID,
    Category_ID
)
VALUES

(
    1001,
    'New Technology Is Changing Modern Education',
    'Technology is rapidly changing the way students learn. Digital platforms, artificial intelligence and online resources are becoming important parts of modern education.',
    '2026-08-15 09:00:00',
    '2026-08-16 11:00:00',
    'Published',
    '2026-08-16 10:30:00',
    '2026-08-16 12:00:00',
    'Article reviewed and approved.',
    102,
    202,
    7
),

(
    1002,
    'Bangladesh Prepares for Major International Tournament',
    'Bangladesh is preparing for an upcoming international sporting tournament. Players and coaches are focusing on training and preparation.',
    '2026-08-17 10:00:00',
    '2026-08-17 15:00:00',
    'Published',
    '2026-08-17 14:30:00',
    '2026-08-17 16:00:00',
    'Good article. Minor grammatical corrections made.',
    103,
    203,
    2
),

(
    1003,
    'New Economic Policies Expected to Boost Business',
    'Several new economic policies are expected to influence businesses and investors in the coming months.',
    '2026-08-18 09:30:00',
    NULL,
    'Pending',
    NULL,
    NULL,
    NULL,
    104,
    NULL,
    4
),

(
    1004,
    'Government Announces New Digital Initiative',
    'The government has announced a new digital initiative aimed at improving public services through technology.',
    '2026-08-18 14:00:00',
    '2026-08-19 10:00:00',
    'Published',
    '2026-08-19 09:30:00',
    '2026-08-19 11:00:00',
    'Approved after minor editing.',
    101,
    201,
    1
),

(
    1005,
    'Local Team Wins Dramatic Final',
    'A dramatic final ended with a memorable victory for the local team after an exciting contest.',
    '2026-08-19 16:00:00',
    NULL,
    'Draft',
    NULL,
    NULL,
    NULL,
    103,
    NULL,
    2
);


-- =========================================================
-- TAG
-- =========================================================

INSERT INTO TAG
(Tag_ID, Tag_Name)
VALUES
(1, 'Bangladesh'),
(2, 'Technology'),
(3, 'Education'),
(4, 'Sports'),
(5, 'Politics'),
(6, 'Business'),
(7, 'International'),
(8, 'Digital'),
(9, 'Government'),
(10, 'AI');


-- =========================================================
-- ARTICLE_TAG
-- =========================================================

INSERT INTO ARTICLE_TAG
(Article_ID, Tag_ID)
VALUES
(1001, 2),
(1001, 3),
(1001, 8),
(1001, 10),

(1002, 1),
(1002, 4),
(1002, 7),

(1003, 1),
(1003, 6),
(1003, 5),

(1004, 1),
(1004, 5),
(1004, 8),
(1004, 9),

(1005, 1),
(1005, 4);


-- =========================================================
-- REGISTERED READER
-- =========================================================

INSERT INTO REGISTERED_READER
(Reader_ID, Email, Name, Phone_No, Password, Status)
VALUES
(10001, 'john@gmail.com', 'John Smith', '01712345678', 'john123', 'Active'),
(10002, 'sadia@gmail.com', 'Sadia Rahman', '01823456789', 'sadia123', 'Active'),
(10003, 'karim@gmail.com', 'Karim Hossain', '01934567890', 'karim123', 'Active'),
(10004, 'nabila@gmail.com', 'Nabila Ahmed', '01645678901', 'nabila123', 'Inactive');


-- =========================================================
-- NON-REGISTERED READER
-- =========================================================

INSERT INTO NON_REGISTERED_READER
(Reader_ID, Email, Name, Phone_No, Password, Status)
VALUES
(20001, 'guest1@gmail.com', 'Guest Reader', NULL, NULL, 'Active'),
(20002, NULL, 'Anonymous Reader', NULL, NULL, 'Active');


-- =========================================================
-- SUBSCRIPTION
-- =========================================================

INSERT INTO SUBSCRIPTION
(Subscription_ID, Name, Code, Frequency, Status, Time, Expire_Date)
VALUES
(1, 'Monthly Premium', 'MONTHLY', 'Monthly', 'Active', '00:00:00', '2026-12-31'),
(2, 'Quarterly Premium', 'QUARTERLY', 'Quarterly', 'Active', '00:00:00', '2027-02-28'),
(3, 'Annual Premium', 'ANNUAL', 'Yearly', 'Active', '00:00:00', '2027-08-20');


-- =========================================================
-- READER_SUBSCRIPTION
-- =========================================================

INSERT INTO READER_SUBSCRIPTION
(Reader_ID, Subscription_ID, Subscribe_Date, Expire_Date, Status)
VALUES
(10001, 1, '2026-08-01', '2026-09-01', 'Active'),
(10002, 3, '2026-07-15', '2027-07-15', 'Active'),
(10003, 2, '2026-08-10', '2026-11-10', 'Active');


-- =========================================================
-- COMMENT
-- =========================================================

INSERT INTO COMMENT
(Comment_ID, Reader_ID, Article_ID, Comment_Text, Time_Stamp, Status)
VALUES
(1, 10001, 1001, 'Very informative article.', '2026-08-16 14:20:00', 'Visible'),
(2, 10002, 1001, 'Technology is definitely changing education.', '2026-08-16 15:10:00', 'Visible'),
(3, 10003, 1002, 'Looking forward to the tournament.', '2026-08-17 18:00:00', 'Visible'),
(4, 10001, 1004, 'This initiative could be very useful.', '2026-08-19 13:00:00', 'Visible');


-- =========================================================
-- BOOKMARK
-- =========================================================

INSERT INTO BOOKMARK
(Reader_ID, Article_ID, Bookmarked_At)
VALUES
(10001, 1001, '2026-08-16 14:30:00'),
(10001, 1004, '2026-08-19 13:10:00'),
(10002, 1002, '2026-08-17 19:00:00'),
(10003, 1001, '2026-08-16 20:00:00');



ALTER TABLE WEBSITE_ADMIN
ADD COLUMN Password VARCHAR(255);

ALTER TABLE REPORTER
ADD COLUMN Password VARCHAR(255);

ALTER TABLE EDITOR
ADD COLUMN Password VARCHAR(255);

UPDATE WEBSITE_ADMIN
SET Password = 'admin123'
WHERE Admin_ID = 1;

UPDATE WEBSITE_ADMIN
SET Password = 'admin456'
WHERE Admin_ID = 2;


UPDATE REPORTER
SET Password = 'reporter123'
WHERE Staff_ID = 101;

UPDATE REPORTER
SET Password = 'reporter456'
WHERE Staff_ID = 102;

UPDATE REPORTER
SET Password = 'reporter789'
WHERE Staff_ID = 103;

UPDATE REPORTER
SET Password = 'reporter000'
WHERE Staff_ID = 104;


UPDATE EDITOR
SET Password = 'editor123'
WHERE Staff_ID = 201;

UPDATE EDITOR
SET Password = 'editor456'
WHERE Staff_ID = 202;

UPDATE EDITOR
SET Password = 'editor789'
WHERE Staff_ID = 203;


ALTER TABLE ARTICLE MODIFY Article_ID INT AUTO_INCREMENT;
