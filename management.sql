-- employeeテーブル
DROP TABLE IF EXISTS employee_users;
CREATE TABLE employee_users (
employee_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
employee_name VARCHAR( 64 ) NOT NULL COMMENT '従業員氏名',
employee_code VARCHAR( 11 ) NOT NULL UNIQUE COMMENT '従業員コードUNIQUE',
employee_password VARCHAR( 255 ) NOT NULL COMMENT 'パスワード：password_hash()関数利用',
employee_section tinyint unsigned NOT NULL COMMENT '部署：0/営業 1/総務 2/経理 3/その他',
employee_role tinyint unsigned NOT NULL COMMENT '役職：0/一般 1/管理職 2/役員',
created_at datetime NOT NULL COMMENT '登録日',
updated_at datetime NOT NULL COMMENT '更新日',
UNIQUE (employee_code)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1管理者を意味するテーブル';

-- １件「全権限管理者」を作成しておく
INSERT INTO employee_users (employee_id, employee_name, employee_code, employee_password, employee_section, employee_role, created_at, updated_at) VALUES('1', '新森雅浩', '0001', '$2y$10$76lFrZipAPh/JJBtcWXO/e37oRi/iXRda6QSRRaU.0/i/KSf0flbC', 3, 2, now(), now());


-- 管理者ユーザロックテーブルの作成
DROP TABLE IF EXISTS employee_login_lock;
CREATE TABLE employee_login_lock(
employee_id varbinary(255) NOT NULL COMMENT '識別するためのID',
error_count tinyint unsigned NOT NULL COMMENT 'ログインエラー回数(ログイン成功したらリセット)',
lock_time datetime NOT NULL COMMENT 'ロック時間。0000-00-00 00:00:00ならロックされていない。',
PRIMARY KEY(`employee_id`)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1ユーザのロック状態を意味するテーブル';


adminテーブル　//管理者登録
CREATE TABLE kintai. admins (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
username VARCHAR( 64 ) NOT NULL ,
email VARCHAR( 128 ) NOT NULL ,
password VARCHAR( 100 ) NOT NULL ,
created_at datetime NOT NULL ,
updated_at datetime NOT NULL ,
UNIQUE (email)
);

workテーブル　//勤怠管理
CREATE TABLE work (
work_id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY ,
work_employee_id INT( 11 ) NOT NULL COMMENT '従業員id',
form VARCHAR( 64 ) DEFAULT NULL COMMENT '出勤フラグ＊出勤時に出勤と記録',
date date NOT NULL COMMENT '出勤時の年月日登録',
start_time time NOT NULL COMMENT '出勤時刻',
end_time time NULL COMMENT '退勤時刻',
break_time time NOT NULL COMMENT '休憩時刻',
created_at datetime NOT NULL COMMENT '登録時の年月日日時分秒',
updated_at datetime NOT NULL COMMENT '更新時の年月日日時分秒'
);

scheduleテーブル　//予定表
CREATE TABLE kintai.schedule (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
user_id INT( 11 ) NOT NULL ,
date date NOT NULL ,
category VARCHAR( 64 ) NOT NULL ,
plan TEXT,
created_at datetime NOT NULL,
updated_at datetime NOT NULL
);

//事前に登録が必要
INSERT INTO work(user_id,date) VALUES(1,'2018-12-01'),(1,'2018-12-02'),(1,'2018-12-03'),(1,'2018-12-04'),(1,'2018-12-05'),(1,'2018-12-06'),(1,'2018-12-12'),(1,'2018-12-08'),
(1,'2018-12-09'),(1,'2018-12-10'),(1,'2018-12-11'),(1,'2018-12-12'),(1,'2018-12-13'),(1,'2018-12-14'),(1,'2018-12-15'),(1,'2018-12-16'),(1,'2018-12-17'),(1,'2018-12-18'),
(1,'2018-12-19'),(1,'2018-12-20'),(1,'2018-12-21'),(1,'2018-12-22'),(1,'2018-12-23'),(1,'2018-12-24'),(1,'2018-12-25'),(1,'2018-12-26'),(1,'2018-12-27'),(1,'2018-12-28'),(1,'2018-12-29'),(1,'2018-12-30'),(1,'2018-12-31');

INSERT INTO work(user_id,date) VALUES(1,'2019-1-01'),(1,'2019-01-02'),(1,'2019-01-03'),(1,'2019-01-04'),(1,'2019-01-05'),(1,'2019-01-06'),(1,'2019-01-12'),(1,'2019-01-08'),
(1,'2019-01-09'),(1,'2019-01-10'),(1,'2019-01-11'),(1,'2019-01-12'),(1,'2019-01-13'),(1,'2019-01-14'),(1,'2019-01-15'),(1,'2019-01-16'),(1,'2019-01-17'),(1,'2019-01-18'),
(1,'2019-01-19'),(1,'2019-01-20'),(1,'2019-01-21'),(1,'2019-01-22'),(1,'2019-01-23'),(1,'2019-01-24'),(1,'2019-01-25'),(1,'2019-01-26'),(1,'2019-01-27'),(1,'2019-01-28'),(1,'2019-01-29'),(1,'2019-01-30'),(1,'2019-01-31');


INSERT INTO work(user_id,date) VALUES(1,'2019-1-01'),(1,'2019-02-02'),(1,'2019-02-03'),(1,'2019-02-04'),(1,'2019-02-05'),(1,'2019-02-06'),(1,'2019-02-12'),(1,'2019-02-08'),
(1,'2019-02-09'),(1,'2019-02-10'),(1,'2019-02-11'),(1,'2019-02-12'),(1,'2019-02-13'),(1,'2019-02-14'),(1,'2019-02-15'),(1,'2019-02-16'),(1,'2019-02-17'),(1,'2019-02-18'),
(1,'2019-02-19'),(1,'2019-02-20'),(1,'2019-02-21'),(1,'2019-02-22'),(1,'2019-02-23'),(1,'2019-02-24'),(1,'2019-02-25'),(1,'2019-02-26'),(1,'2019-02-27'),(1,'2019-02-28');


