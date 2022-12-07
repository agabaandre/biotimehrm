
-- person_duty_sums
SELECT
    `duty_rosta`.`id` AS `id`,
    CONCAT(
        `ihrisdata`.`surname`,
        ' ',
        `ihrisdata`.`firstname`,
        ' '
    ) AS `fullname`,
    `ihrisdata`.`othername` AS `othername`,
    `ihrisdata`.`job` AS `job`,
    `duty_rosta`.`facility_id` AS `facility_id`,
    `ihrisdata`.`facility` AS `facility_name`,
    `duty_rosta`.`department_id` AS `department_id`,
    `duty_rosta`.`ihris_pid` AS `ihris_pid`,
    `duty_rosta`.`schedule_id` AS `schedule_id`,
    DATE_FORMAT(`duty_rosta`.`duty_date`, '%Y-%m') AS `duty_date`,
    CASE WHEN `schedules`.`letter` = 'D' THEN COUNT(`schedules`.`letter`)
END AS `D`,
CASE WHEN `schedules`.`letter` = 'E' THEN COUNT(`schedules`.`letter`)
END AS `E`,
CASE WHEN `schedules`.`letter` = 'N' THEN COUNT(`schedules`.`letter`)
END AS `N`,
CASE WHEN `schedules`.`letter` = 'O' THEN COUNT(`schedules`.`letter`)
END AS `O`,
CASE WHEN `schedules`.`letter` = 'A' THEN COUNT(`schedules`.`letter`)
END AS `A`,
CASE WHEN `schedules`.`letter` = 'S' THEN COUNT(`schedules`.`letter`)
END AS `S`,
CASE WHEN `schedules`.`letter` = 'M' THEN COUNT(`schedules`.`letter`)
END AS `M`,
CASE WHEN `schedules`.`letter` = 'Z' THEN COUNT(`schedules`.`letter`)
END AS `Z`
FROM
    `duty_rosta`
JOIN schedules ON duty_rosta.schedule_id = schedules.schedule_id JOIN ihrisdata ON ihrisdata.ihris_pid=duty_rosta.ihris_pid
GROUP BY
    `duty_rosta`.`ihris_pid`,
    DATE_FORMAT(`duty_rosta`.`duty_date`, '%Y-%m'),
    `duty_rosta`.`schedule_id`


-- person_attend_sums
SELECT
    `actuals`.`id` AS `id`,
    CONCAT(
        `ihrisdata`.`surname`,
        ' ',
        `ihrisdata`.`firstname`,
        ' '
    ) AS `fullname`,
    `ihrisdata`.`othername` AS `othername`,
    `ihrisdata`.`job` AS `job`,
    `actuals`.`facility_id` AS `facility_id`,
    `ihrisdata`.`facility` AS `facility_name`,
    `actuals`.`department_id` AS `department_id`,
    `actuals`.`ihris_pid` AS `ihris_pid`,
    `actuals`.`schedule_id` AS `schedule_id`,
    DATE_FORMAT(`actuals`.`date`, '%Y-%m') AS `duty_date`,
CASE WHEN `schedules`.`letter` = 'P' THEN COUNT(`schedules`.`letter`)
END AS `P`,
CASE WHEN `schedules`.`letter` = 'R' THEN COUNT(`schedules`.`letter`)
END AS `R`,
CASE WHEN `schedules`.`letter` = 'O' THEN COUNT(`schedules`.`letter`)
END AS `O`,
CASE WHEN `schedules`.`letter` = 'L' THEN COUNT(`schedules`.`letter`)
END AS `L`,
CASE WHEN `schedules`.`letter` = 'H' THEN COUNT(`schedules`.`letter`)
END AS `H`,
CASE WHEN `schedules`.`letter` = 'X' THEN COUNT(`schedules`.`letter`)
END AS `X`

FROM
    `actuals`
JOIN schedules ON actuals.schedule_id = schedules.schedule_id JOIN ihrisdata ON ihrisdata.ihris_pid=actuals.ihris_pid
GROUP BY
    `actuals`.`ihris_pid`,
    DATE_FORMAT(`actuals`.`date`, '%Y-%m'),
    `actuals`.`schedule_id`

-- person_attend_final reporting view
SELECT
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
    `a`.`ihris_pid`

-- person_duty_final

 SELECT
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
            `person_duty_sums` `a`
        GROUP BY
            `a`.`duty_date`,
            `a`.`ihris_pid`



   
   select concat(`mohattendance`.`ihrisdata`.`surname`,' ',`mohattendance`.`ihrisdata`.`firstname`,' ') AS `fullname`,`mohattendance`.`ihrisdata`.`othername` AS `othername`,`mohattendance`.`ihrisdata`.`ihris_pid` AS `ihris_pid`,`mohattendance`.`ihrisdata`.`job` AS `job`,`mohattendance`.`ihrisdata`.`facility` AS `facility`,`mohattendance`.`ihrisdata`.`facility` AS `facility_name`,`dutyreport`.`schedule_id` AS `schedule_id`,`dutyreport`.`duty_date` AS `duty_date`,`dutyreport`.`entry_id` AS `entry_id`,`mohattendance`.`ihrisdata`.`facility_id` AS `facility_id`,max(`dutyreport`.`day1`) AS `day1`,max(`dutyreport`.`day2`) AS `day2`,max(`dutyreport`.`day3`) AS `day3`,max(`dutyreport`.`day4`) AS `day4`,max(`dutyreport`.`day5`) AS `day5`,max(`dutyreport`.`day6`) AS `day6`,max(`dutyreport`.`day7`) AS `day7`,max(`dutyreport`.`day8`) AS `day8`,max(`dutyreport`.`day9`) AS `day9`,max(`dutyreport`.`day10`) AS `day10`,max(`dutyreport`.`day11`) AS `day11`,max(`dutyreport`.`day12`) AS `day12`,max(`dutyreport`.`day13`) AS `day13`,max(`dutyreport`.`day14`) AS `day14`,max(`dutyreport`.`day15`) AS `day15`,max(`dutyreport`.`day16`) AS `day16`,max(`dutyreport`.`day17`) AS `day17`,max(`dutyreport`.`day18`) AS `day18`,max(`dutyreport`.`day19`) AS `day19`,max(`dutyreport`.`day20`) AS `day20`,max(`dutyreport`.`day21`) AS `day21`,max(`dutyreport`.`day22`) AS `day22`,max(`dutyreport`.`day23`) AS `day23`,max(`dutyreport`.`day24`) AS `day24`,max(`dutyreport`.`day25`) AS `day25`,max(`dutyreport`.`day26`) AS `day26`,max(`dutyreport`.`day27`) AS `day27`,max(`dutyreport`.`day28`) AS `day28`,max(`dutyreport`.`day29`) AS `day29`,max(`dutyreport`.`day30`) AS `day30`,max(`dutyreport`.`day31`) AS `day31` from (`mohattendance`.`dutyreport` join `mohattendance`.`ihrisdata` on(`mohattendance`.`ihrisdata`.`ihris_pid` = `dutyreport`.`ihris_pid`)) group by `mohattendance`.`ihrisdata`.`ihris_pid`


--    New rosta view
-- // new rosta view
  SELECT
    `duty_rosta`.`entry_id` AS `entry_id`,
    CONCAT(
        `ihrisdata`.`surname`,
        ' ',
        `ihrisdata`.`firstname`
    ) AS `fullname`,
    `ihrisdata`.`othername` AS `othername`,
    `duty_rosta`.`id` AS `id`,
    `ihrisdata`.`department_id` AS `department`,
    `ihrisdata`.`job` AS `job`,
    `ihrisdata`.`facility` AS `facility`,
    `duty_rosta`.`facility_id` AS `facility_id`,
    `ihrisdata`.`district_id` AS `district_id`,
    `ihrisdata`.`district` AS `district`,
    `duty_rosta`.`department_id` AS `department_id`,
    `duty_rosta`.`ihris_pid` AS `ihris_pid`,
    `duty_rosta`.`schedule_id` AS `schedule_id`,
    `duty_rosta`.`color` AS `color`,
    `duty_rosta`.`duty_date` AS `duty_date`,
    DATE_FORMAT(`duty_rosta`.`duty_date`, '%Y-%m') AS `period`,
    `duty_rosta`.`end` AS `end`,
    `duty_rosta`.`allDay` AS `allDay`,
    CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-01' THEN `duty_rosta`.`duty_date`
END AS `day1`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-02' THEN `duty_rosta`.`duty_date`
END AS `day2`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-03' THEN `duty_rosta`.`duty_date`
END AS `day3`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-04' THEN `duty_rosta`.`duty_date`
END AS `day4`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-05' THEN `duty_rosta`.`duty_date`
END AS `day5`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-06' THEN `duty_rosta`.`duty_date`
END AS `day6`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-07' THEN `duty_rosta`.`duty_date`
END AS `day7`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-08' THEN `duty_rosta`.`duty_date`
END AS `day8`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-09' THEN `duty_rosta`.`duty_date`
END AS `day9`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-10' THEN `duty_rosta`.`duty_date`
END AS `day10`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-11' THEN `duty_rosta`.`duty_date`
END AS `day11`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-12' THEN `duty_rosta`.`duty_date`
END AS `day12`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-13' THEN `duty_rosta`.`duty_date`
END AS `day13`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-14' THEN `duty_rosta`.`duty_date`
END AS `day14`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-15' THEN `duty_rosta`.`duty_date`
END AS `day15`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-16' THEN `duty_rosta`.`duty_date`
END AS `day16`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-17' THEN `duty_rosta`.`duty_date`
END AS `day17`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-18' THEN `duty_rosta`.`duty_date`
END AS `day18`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-19' THEN `duty_rosta`.`duty_date`
END AS `day19`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-20' THEN `duty_rosta`.`duty_date`
END AS `day20`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-21' THEN `duty_rosta`.`duty_date`
END AS `day21`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-22' THEN `duty_rosta`.`duty_date`
END AS `day22`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-23' THEN `duty_rosta`.`duty_date`
END AS `day23`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-24' THEN `duty_rosta`.`duty_date`
END AS `day24`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-25' THEN `duty_rosta`.`duty_date`
END AS `day25`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-26' THEN `duty_rosta`.`duty_date`
END AS `day26`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-27' THEN `duty_rosta`.`duty_date`
END AS `day27`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-28' THEN `duty_rosta`.`duty_date`
END AS `day28`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-29' THEN `duty_rosta`.`duty_date`
END AS `day29`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-30' THEN `duty_rosta`.`duty_date`
END AS `day30`,
CASE WHEN `duty_rosta`.`duty_date` LIKE '%-%-31' THEN `duty_rosta`.`duty_date`
END AS `day31`
FROM
    `duty_rosta`,
    `ihrisdata`
WHERE
    `ihrisdata`.`ihris_pid` = `duty_rosta`.`ihris_pid`;