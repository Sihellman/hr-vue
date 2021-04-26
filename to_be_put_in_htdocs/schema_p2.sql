/*
 *    Database:local_api_p2 
 *      Author: Jonas Neuhengen
 *        Date: 2020-12-21
 * Description: A database to use for Project 2 of the 
 *              Client-Based Web Development course.
 */



/******************************************************
  DATABASE / USER CREATION
 ******************************************************/

DROP DATABASE IF EXISTS local_api_p2;
CREATE DATABASE local_api_p2;


USE local_api_p2;


-- Create a limited-access (_l) user just for this DB, to minimize damage if
--   compromised
GRANT SELECT,INSERT,UPDATE,DELETE ON local_api_p2.*
TO local_api_p2_l@localhost IDENTIFIED BY 'a0bivlslev';




/******************************************************
  TABLE CREATION
 ******************************************************/

DROP TABLE IF EXISTS Employee;
CREATE TABLE Employee (
  employee_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_id          INT UNSIGNED NOT NULL,
  fname           VARCHAR(255),
  lname           VARCHAR(255),
  email           VARCHAR(255),
  PRIMARY KEY (employee_id)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS Job;
CREATE TABLE Job (
  job_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(255),
  PRIMARY KEY (job_id)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS Competency;
CREATE TABLE Competency (
  competency_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(255),
  descr           TEXT,
  PRIMARY KEY (competency_id)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS Job_Competency;
CREATE TABLE Job_Competency (
  job_id          INT UNSIGNED NOT NULL,
  competency_id   INT UNSIGNED NOT NULL,
  PRIMARY KEY (job_id, competency_id)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS Employee_Job_Competency;
CREATE TABLE Employee_Job_Competency (
  employee_id     INT UNSIGNED NOT NULL,
  job_id          INT UNSIGNED NOT NULL,
  competency_id   INT UNSIGNED NOT NULL,
  score           TINYINT UNSIGNED NOT NULL COMMENT 'Rating from 1-5',
  PRIMARY KEY (employee_id, job_id, competency_id)
) ENGINE=InnoDB;




/******************************************************
  FOREIGN KEYS
 ******************************************************/

ALTER TABLE Employee ADD FOREIGN KEY (job_id)
REFERENCES Job (job_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Job_Competency ADD FOREIGN KEY (job_id)
REFERENCES Job (job_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Job_Competency ADD FOREIGN KEY (competency_id)
REFERENCES Competency (competency_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Employee_Job_Competency ADD FOREIGN KEY (employee_id)
REFERENCES Employee (employee_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Employee_Job_Competency ADD FOREIGN KEY (job_id)
REFERENCES Job (job_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Employee_Job_Competency ADD FOREIGN KEY (competency_id)
REFERENCES Competency (competency_id) ON DELETE CASCADE ON UPDATE CASCADE;




/******************************************************
  INITIAL DATA
 ******************************************************/



INSERT INTO Job (job_id, name) VALUES
  (1,'Human Resources Director'),
  (2,'Plant Manager'),
  (3,'Executive Assistant'),
  (4,'Shippping Supervisor'),
  (5,'Warehouse Supervisor'),
  (6,'Industrial Engineer'),
  (7,'Plant Nurse'),
  (8,'Shipping Clerk');


INSERT INTO Employee (employee_id, job_id, fname, lname, email) VALUES
  (1, 3, 'Mairi', 'Kunkel', 'm.kunkel@example.com'),
  (2, 4, 'Oscar', 'McCreery', 'o.mccreery@example.com'),
  (3, 2, 'Gustav', 'Haraldsson', 'g.haraldsson@example.com'),
  (4, 3, 'Alex', 'Habich', 'a.habich@example.com'),
  (5, 1, 'Anna', 'Grier', 'a.grier@example.com'),
  (6, 7, 'Mariella', 'Kavanah', 'm.kavanah@example.com'),
  (7, 5, 'Eadwig', 'Cason', 'e.cason@example.com'),
  (8, 8, 'Imrich', 'McClelland', 'i.mcclelland@example.com'),
  (9, 7, 'Puck', 'Pasternak', 'p.pasternak@example.com'),
  (10, 6, 'Elias', 'Chaplin', 'e.chaplin@example.com'),
  (11, 6, 'Seetha', 'Motta', 's.motta@example.com'),
  (12, 3, 'Akira', 'Bozhidarov', 'a.bozhidarov@example.com'),
  (13, 8, 'Rajesh', 'Adema', 'r.adema@example.com'),
  (14, 4, 'Rostam', 'Banner', 'r.banner@example.com'),
  (15, 5, 'Immanuel', 'Naumov', 'i.naumov@example.com'),
  (16, 5, 'Grigory', 'Novikov', 'g.novikov@example.com'),
  (17, 2, 'Lera', 'Kovalev', 'l.kovalev@example.com'),
  (18, 8, 'Chaim', 'Kovalev', 'c.kovalev@example.com'),
  (19, 1, 'Basia', 'Naumov', 'b.naumov@example.com');


INSERT INTO Competency (competency_id, name, descr) VALUES
  (1, 'Builds Relationships', 'Leverages relationships to influence behaviors that drive business growth and success. Builds networks by promoting collaboration across disciplines, departments, functions and levels. Can command attention when communicating with others.'),
  (2, 'Drive For Results', 'Demonstrates understanding of the direction of the company?s future and keeps focused on the vision. Translates vision into competitive and breakthrough business strategies. Develops long-term objectives and plans weighing needs and concerns of relevant departments. Understands relevant business drivers and uses financial or market data to set clear strategy and direction.'),
  (3, 'Model The Way', 'Encourages fairness, honesty and trust within and outside the company; follows through on commitments. Behaves in a professional manner when responding to situations or people at all levels of the company. Reinforces the value of fierce conversation through open and honest debate. Communicates with confidence and steadiness; displays poise during difficult times. Seeks to understand own strengths and weaknesses.'),
  (4, 'Organizational Agility', 'Strives to anticipate problems in advance and takes preventive action. Can work under pressure to make quality decisions even without all the information; emphasizes the value of speed. Creates a flexible environment. Rallies employees to support organizational changes or new initiatives. Can handle ambiguity and uncertainty. Seizes new opportunities; steps up to necessary actions that may be unpopular when believe it is right for the business.'),
  (5, 'Strategic Focus', 'Demonstrates understanding of the direction of the company?s future and keeps focused on the vision. Translates vision into competitive and breakthrough business strategies. Develops long-term objectives and plans, weighing needs and concerns of relevant departments. Understands relevant business drivers and uses financial or market data to set clear strategy and direction.'),
  (6, 'Talent Management', 'Identify and align talent with the organizations core values and leadership competency. Develop and implement measures and rewards system for assessing employee performance, development opportunities and anticipate future organizational development needs of the business.'),
  (7, 'Builds High Performing Teams', 'Hires and develops top talent. Knows the talent within the company and can accurately identify leadership potential. Encourages and supports those who challenge the status quo. Empowers others to take on greater responsibility. Coaches others to improve performance. Aligns talent and resources to achieve business objectives.'),
  (8, 'Process Oriented', 'Champion of center lining. Ability to notice relevant process details, understand their root causes, fit a solution into the big picture and act accordingly.'),
  (9, 'Broad Business Knowledge', 'Assesses potential threats and opportunities. Develops plans to improve performance or gain competitive advantage. Knowledge of the external market/industry and key competitors. Uses financial and statistical data to make fact-based decisions. Understands business drivers of financials and profitability.'),
  (10, 'Customer Support', 'The knowledge, understanding, and application of the processes used to provide quality service to internal and external customers at all points of interface.'),
  (11, 'Organizational Agility', 'Strives to anticipate problems in advance and takes preventive action. Can work under pressure to make quality decisions even without all the information; emphasizes the value of speed. Creates a flexible environment. Rallies employees to support organizational changes or new initiatives. Can handle ambiguity and uncertainty. Seizes new opportunities; steps up to necessary actions that may be unpopular when believe it is right for the business.'),
  (12, 'Supply Chain Planning', 'Utilizes knowledge of supply planning processes and tools to increase operational efficiencies, reduce costs and improve customer satisfaction.'),
  (13, 'Traffic Operations', 'Designing and implementing transportation operations strategies for transporting goods to customers.'),
  (14, 'Engineering', 'Develops the manufacturing engineering strategy; determining the gaps between current capabilities and future requirements then identifying and executing capital projects to close these gaps to ensure plants operate at maximum equipment utilization and efficiency.'),
  (15, 'Risk Taking & Innovation', 'Strives to anticipate problems in advance and takes preventive action. Can work under pressure to make quality decisions even without all the information; emphasizes the value of speed. Creates a flexible environment. Rallies employees to support organizational changes or new initiatives. Can handle ambiguity and uncertainty. Seizes new opportunities; steps up to necessary actions that may be unpopular when believe it is right for the business.'),
  (16, 'EHS Focus', 'Maintains and develops environment, health and safety expertise, and integrates appropriate regulations, standards and practices to enforce a strong safety culture.');


INSERT INTO Job_Competency (job_id, competency_id) VALUES
  (1,  1),
  (1,  2),
  (1,  3),
  (1,  4),
  (1,  5),
  (1,  6),
  (2,  7),
  (2,  1),
  (2,  2),
  (2,  3),
  (2,  8),
  (2,  5),
  (3,  9),
  (3,  1),
  (3,  2),
  (3,  3),
  (3,  5),
  (4,  1),
  (4,  2),
  (4,  3),
  (4,  8),
  (4,  5),
  (5,  7),
  (5,  1),
  (5, 10),
  (5,  2),
  (5, 11),
  (5,  5),
  (5, 12),
  (5, 13),
  (6,  9),
  (6,  1),
  (6,  2),
  (6, 14),
  (6, 15),
  (6,  5),
  (7,  7),
  (7,  1),
  (7,  2),
  (7, 16),
  (7,  3),
  (7, 11),
  (8,  7),
  (8,  1),
  (8, 10),
  (8,  2),
  (8, 11),
  (8,  5),
  (8, 12),
  (8, 13);


INSERT INTO Employee_Job_Competency (employee_id, job_id, competency_id, score) VALUES
  (1, 3, 9, 1),
  (1, 3, 1, 3),
  (1, 3, 2, 4),
  (1, 3, 3, 4),
  (1, 3, 5, 3),
  (2, 4, 1, 1),
  (2, 4, 2, 3),
  (2, 4, 3, 3),
  (2, 4, 8, 4),
  (2, 4, 5, 1),
  (3, 2, 7, 3),
  (3, 2, 1, 1),
  (3, 2, 2, 5),
  (3, 2, 3, 1),
  (3, 2, 8, 4),
  (3, 2, 5, 5),
  (4, 3, 9, 4),
  (4, 3, 1, 1),
  (4, 3, 2, 2),
  (4, 3, 3, 2),
  (4, 3, 5, 2),
  (5, 1, 1, 1),
  (5, 1, 2, 4),
  (5, 1, 3, 1),
  (5, 1, 4, 5),
  (5, 1, 5, 2),
  (5, 1, 6, 3),
  (6, 7, 7, 2),
  (6, 7, 1, 4),
  (6, 7, 2, 2),
  (6, 7, 16, 5),
  (6, 7, 3, 4),
  (6, 7, 11, 1),
  (7, 5, 7, 2),
  (7, 5, 1, 4),
  (7, 5, 10, 5),
  (7, 5, 2, 2),
  (7, 5, 11, 3),
  (7, 5, 5, 2),
  (7, 5, 12, 4),
  (7, 5, 13, 4),
  (8, 8, 7, 1),
  (8, 8, 1, 1),
  (8, 8, 10, 3),
  (8, 8, 2, 2),
  (8, 8, 11, 3),
  (8, 8, 5, 5),
  (8, 8, 12, 2),
  (8, 8, 13, 4),
  (9, 7, 7, 2),
  (9, 7, 1, 2),
  (9, 7, 2, 1),
  (9, 7, 16, 3),
  (9, 7, 3, 4),
  (9, 7, 11, 3),
  (10, 6, 9, 2),
  (10, 6, 1, 1),
  (10, 6, 2, 1),
  (10, 6, 14, 3),
  (10, 6, 15, 5),
  (10, 6, 5, 3),
  (11, 6, 9, 3),
  (11, 6, 1, 5),
  (11, 6, 2, 4),
  (11, 6, 14, 1),
  (11, 6, 15, 5),
  (11, 6, 5, 1),
  (12, 3, 9, 4),
  (12, 3, 1, 1),
  (12, 3, 2, 1),
  (12, 3, 3, 2),
  (12, 3, 5, 5),
  (13, 8, 7, 2),
  (13, 8, 1, 5),
  (13, 8, 10, 3),
  (13, 8, 2, 1),
  (13, 8, 11, 3),
  (13, 8, 5, 5),
  (13, 8, 12, 5),
  (13, 8, 13, 1),
  (14, 4, 1, 2),
  (14, 4, 2, 5),
  (14, 4, 3, 4),
  (14, 4, 8, 5),
  (14, 4, 5, 3),
  (15, 5, 7, 5),
  (15, 5, 1, 2),
  (15, 5, 10, 5),
  (15, 5, 2, 5),
  (15, 5, 11, 3),
  (15, 5, 5, 3),
  (15, 5, 12, 2),
  (15, 5, 13, 5),
  (16, 5, 7, 5),
  (16, 5, 1, 4),
  (16, 5, 10, 5),
  (16, 5, 2, 3),
  (16, 5, 11, 5),
  (16, 5, 5, 5),
  (16, 5, 12, 3),
  (16, 5, 13, 1),
  (17, 2, 7, 4),
  (17, 2, 1, 4),
  (17, 2, 2, 3),
  (17, 2, 3, 1),
  (17, 2, 8, 1),
  (17, 2, 5, 1),
  (18, 8, 7, 3),
  (18, 8, 1, 5),
  (18, 8, 10, 3),
  (18, 8, 2, 3),
  (18, 8, 11, 5),
  (18, 8, 5, 4),
  (18, 8, 12, 3),
  (18, 8, 13, 2),
  (19, 1, 1, 5),
  (19, 1, 2, 4),
  (19, 1, 3, 1),
  (19, 1, 4, 4),
  (19, 1, 5, 1),
  (19, 1, 6, 2);




