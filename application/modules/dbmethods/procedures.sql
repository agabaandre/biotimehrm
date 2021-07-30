-- biotime clockin
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `biotime_clk`()
BEGIN

REPLACE INTO clk_log (
    entry_id,
    ihris_pid,
    facility_id,
    time_in,
    date,
    location,
    source,
    facility)
    SELECT
    
    concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) as entry_id,
    ihrisdata.ihris_pid,
    facility_id, 
    punch_time,
    DATE(biotime_data.punch_time) as date,
    area_alias,
    'BIO-TIME',
    ihrisdata.facility
    from  biotime_data, ihrisdata where (biotime_data.emp_code=ihrisdata.card_number or biotime_data.ihris_pid=ihrisdata.ihris_pid) AND (punch_state='0' OR punch_state='Check In') AND concat(DATE(biotime_data.punch_time),ihrisdata.ihris_pid) NOT IN (SELECT DISTINCT(entry_id) from clk_log);

END$$
DELIMITER ;

-- Queried by dutyroster an d attendance
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `duty_report`(IN `date_range` VARCHAR(30), IN `facility` VARCHAR(100), IN `lim` INT(10), IN `sta` INT(10), IN `search` TEXT)
SELECT CONCAT(surname,' ',firstname,' ') as fullname, ihrisdata.othername, ihrisdata.ihris_pid,job,facility,ihrisdata.facility as facility_name,report.day1,report.day2,report.day3,report.day4,report.day5,report.day6,report.day7,report.day8,report.day9,report.day10,report.day11,report.day12,report.day13,report.day14,report.day15,report.day16,report.day17,report.day18,report.day19,report.day20,report.day21,report.day22,report.day23,report.day24,report.day25,report.day26,report.day27,report.day28,report.day29,report.day30,report.day31  FROM ihrisdata ,
(select ihris_pid,schedule_id,dutyreport.duty_date, dutyreport.entry_id,facility_id ,max(dutyreport.day1) as day1,max(dutyreport.day2)as day2,max(dutyreport.day3)as day3,max(dutyreport.day4)as day4,max(dutyreport.day5)as day5,max(dutyreport.day6)as day6,max(dutyreport.day7)as day7,max(dutyreport.day8)as day8,max(dutyreport.day9)as day9,max(dutyreport.day10)as day10,
		max(dutyreport.day11)as day11,max(dutyreport.day12)as day12,max(dutyreport.day13)as day13,max(dutyreport.day14)as day14,max(dutyreport.day15)as day15,max(dutyreport.day16)as day16,max(dutyreport.day17)as day17,max(dutyreport.day18)as day18,max(dutyreport.day19)as day19,
	 	max(dutyreport.day20)as day20,max(dutyreport.day21)as day21,max(dutyreport.day22)as day22,max(dutyreport.day23)as day23,max(dutyreport.day24)as day24,max(dutyreport.day25)as day25,max(dutyreport.day26)as day26,max(dutyreport.day27)as day27,max(dutyreport.day28)as day28,max(dutyreport.day29)as day29,max(dutyreport.day30)as day30,max(dutyreport.day31)as day31 from dutyreport WHERE (DATE_FORMAT(dutyreport.duty_date, "%Y-%m") =date_range   and dutyreport.facility_id=facility) GROUP BY dutyreport.ihris_pid) report WHERE ihrisdata.ihris_pid=report.ihris_pid  LIMIT lim,sta$$
DELIMITER ;

-- //fill person_att_final table
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `fill_att_sums`()
REPLACE INTO `person_att_final`(
    `entry_id`,
    `ihris_pid`,
    `fullname`,
    `othername`,
    `facility_id`,
    `facility_name`,
    `schedule_id`,
    `duty_date`,
    `job`,
    `P`,
    `O`,
    `L`,
    `R`,
    `X`,
    `H`
)
SELECT
    CONCAT(`a`.`ihris_pid`, '-', `a`.`duty_date`),
    `a`.`ihris_pid` AS `ihris_pid`,
    `a`.`fullname` AS `fullname`,
    `a`.`othername` AS `othername`,
    `a`.`facility_id` AS `facility_id`,
    `a`.`facility_name` AS `facility_name`,
    `a`.`schedule_id` AS `schedule_id`,
    `a`.`duty_date` AS `duty_date`,
    `a`.`job` AS `job`,
    MAX(`a`.`P`) AS `P`,
    MAX(`a`.`O`) AS `O`,
    MAX(`a`.`L`) AS `L`,
    MAX(`a`.`R`) AS `R`,
    MAX(`a`.`X`) AS `X`,
    MAX(`a`.`H`) AS `H`
FROM
    `mohattendance`.`person_attend_sums` `a`
GROUP BY
    `a`.`duty_date`,
    `a`.`ihris_pid`$$
DELIMITER ;

-- //fill person_dut_final table
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `fill_duty_sums`()
INSERT INTO `person_dut_final`(
    `entry_id`,
    `ihris_pid`,
    `fullname`,
    `othername`,
    `facility_id`,
    `facility_name`,
    `schedule_id`,
    `duty_date`,
    `job`,
    `D`,
    `E`,
    `N`,
    `O`,
    `A`,
    `S`,
    `M`,
    `Z`
)
SELECT
    CONCAT(`a`.`ihris_pid`, '-', `a`.`duty_date`),
    `a`.`ihris_pid` AS `ihris_pid`,
    `a`.`fullname` AS `fullname`,
    `a`.`othername` AS `othername`,
    `a`.`facility_id` AS `facility_id`,
    `a`.`facility_name` AS `facility_name`,
    `a`.`schedule_id` AS `schedule_id`,
    `a`.`duty_date` AS `duty_date`,
    `a`.`job` AS `job`,
    MAX(`a`.`D`) AS `D`,
    MAX(`a`.`E`) AS `E`,
    MAX(`a`.`N`) AS `N`,
    MAX(`a`.`O`) AS `O`,
    MAX(`a`.`A`) AS `A`,
    MAX(`a`.`S`) AS `S`,
    MAX(`a`.`M`) AS `M`,
    MAX(`a`.`Z`) AS `Z`
FROM
    `mohattendance`.`person_duty_sums` `a`
GROUP BY
    `a`.`duty_date`,
    `a`.`ihris_pid`$$
DELIMITER ;

-- // 2ihris data attendance
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ihris_share_att`(IN `ymonth` INT(7))
BEGIN
	DECLARE flag TINYINT(1) DEFAULT 0;
    DECLARE names VARCHAR(200) DEFAULT '';
    DECLARE facility VARCHAR(200) DEFAULT '';
    DECLARE pid VARCHAR(200) DEFAULT '';
    DECLARE present VARCHAR(200) DEFAULT '';
    DECLARE offDuty VARCHAR(200) DEFAULT '';
    DECLARE leaves VARCHAR(200) DEFAULT '';
    DECLARE request VARCHAR(200) DEFAULT '';
    
    
    DECLARE attMakers CURSOR FOR

 SELECT ihrisdata.facility_id ,ihrisdata.ihris_pid,CONCAT(ihrisdata.surname,'',ihrisdata.firstname,ihrisdata.othername) FROM ihrisdata WHERE ihrisdata.ihris_pid IN(SELECT distinct actuals.ihris_pid from actuals where actuals.date like CONCAT(@p0,'%'));
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET flag:= 1;
    OPEN attMakers;
    inv_loop: LOOP
    
    
    FETCH attMakers INTO facility,pid,names;
	
    	IF  flag THEN
         LEAVE inv_loop; 
        END IF;
        

SELECT count(actuals.entry_id) into present from actuals
WHERE (actuals.ihris_pid=pid and actuals.schedule_id=22 and actuals.date like CONCAT(@p0,'%'));

SELECT count(actuals.entry_id) into offDuty from actuals
WHERE actuals.ihris_pid=pid and actuals.schedule_id=24 and actuals.date like CONCAT(@p0,'%');

SELECT count(actuals.entry_id) into leaves from actuals
WHERE actuals.ihris_pid=pid and actuals.schedule_id=25 and actuals.date like CONCAT(@p0,'%');

SELECT count(actuals.entry_id) into request from actuals
WHERE actuals.ihris_pid=pid and actuals.schedule_id=23 and actuals.date like CONCAT(@p0,'%');




INSERT into  att_summary VALUES (
    pid,
    CONCAT(@p0,'-01'),
    present,
    offDuty,
    request,
    leaves);
              
    END LOOP;
    CLOSE attMakers;
    
    -- ihris duty roster
   
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ihris_share_duty_sum`(IN `ymonth` VARCHAR(8))
    NO SQL
BEGIN
	DECLARE flag TINYINT(1) DEFAULT 0;
    DECLARE names VARCHAR(200) DEFAULT '';
    DECLARE facility VARCHAR(200) DEFAULT '';
    DECLARE pid VARCHAR(200) DEFAULT '';
    DECLARE dayDuty VARCHAR(200) DEFAULT '';
    DECLARE offDuty VARCHAR(200) DEFAULT '';
    DECLARE eveningDuty VARCHAR(200) DEFAULT '';
    DECLARE nightDuty VARCHAR(200) DEFAULT '';
    DECLARE annualLeave VARCHAR(200) DEFAULT '';
    DECLARE studyLeave VARCHAR(200) DEFAULT '';
    DECLARE maternityLeave VARCHAR(200) DEFAULT '';
    DECLARE otherLeave VARCHAR(200) DEFAULT '';
  
    
    
    DECLARE rosMaker CURSOR FOR

 SELECT DISTINCT ihrisdata.ihris_pid FROM ihrisdata,duty_rosta WHERE ihrisdata.ihris_pid=duty_rosta.ihris_pid AND duty_rosta.duty_date like CONCAT(@p0,'%');
     
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET flag:= 1;
    OPEN rosMaker;
    inv_loop: LOOP
    
    
    FETCH rosMaker INTO pid;
	
    	IF  flag THEN
         LEAVE inv_loop; 
        END IF;
        

SELECT count(duty_rosta.entry_id) into dayDuty from duty_rosta
WHERE (duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=14 and duty_rosta.duty_date like CONCAT(@p0,'%'));


SELECT count(duty_rosta.entry_id) into eveningDuty from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=15 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into nightDuty from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=16 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into offDuty from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=17 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into annualLeave from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=18 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into studyLeave from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=19 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into maternityLeave from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=20 and duty_rosta.duty_date like CONCAT(@p0,'%');

SELECT count(duty_rosta.entry_id) into otherLeave from duty_rosta
WHERE duty_rosta.ihris_pid=pid and duty_rosta.schedule_id=21 and duty_rosta.duty_date like CONCAT(@p0,'%');



INSERT into  dutysummary VALUES (
    pid,
    CONCAT(@p0,'-01'),
    (dayDuty+eveningDuty+nightDuty),
     offDuty,
    (annualLeave+studyLeave+maternityLeave),
     otherLeave);
              
    END LOOP;
    CLOSE rosMaker;
    
    
   
    
END$$
DELIMITER ;
-- fill staffing rate

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `staffing_rate`()
INSERT into staffing_rate (
staff,
date,
facility_id,
facility,
district_id,
district

) 
select count(ihris_pid) AS staff, curdate() AS `date`,`facility_id` AS `facility_id`,`facility` AS `facility`,`district_id` AS `district_id`,`district` AS `district` from `ihrisdata` group by `facility_id`$$
DELIMITER ;