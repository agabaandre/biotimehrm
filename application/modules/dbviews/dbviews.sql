
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