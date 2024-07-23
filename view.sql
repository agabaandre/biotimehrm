select concat(
        `ihrisdata`.`surname`,
        ' ',
        `ihrisdata`.`firstname`,
        ' '
    ) AS `fullname`,
    `ihrisdata`.`othername` AS `othername`,
    `ihrisdata`.`ihris_pid` AS `ihris_pid`,
    `ihrisdata`.`job` AS `job`,
    `ihrisdata`.`facility` AS `facility`,
    `ihrisdata`.`facility` AS `facility_name`,
    `dutyreport`.`schedule_id` AS `schedule_id`,
    `dutyreport`.`duty_date` AS `duty_date`,
    `dutyreport`.`entry_id` AS `entry_id`,
    `ihrisdata`.`facility_id` AS `facility_id`,
    max(`dutyreport`.`day1`) AS `day1`,
    max(`dutyreport`.`day2`) AS `day2`,
    max(`dutyreport`.`day3`) AS `day3`,
    max(`dutyreport`.`day4`) AS `day4`,
    max(`dutyreport`.`day5`) AS `day5`,
    max(`dutyreport`.`day6`) AS `day6`,
    max(`dutyreport`.`day7`) AS `day7`,
    max(`dutyreport`.`day8`) AS `day8`,
    max(`dutyreport`.`day9`) AS `day9`,
    max(`dutyreport`.`day10`) AS `day10`,
    max(`dutyreport`.`day11`) AS `day11`,
    max(`dutyreport`.`day12`) AS `day12`,
    max(`dutyreport`.`day13`) AS `day13`,
    max(`dutyreport`.`day14`) AS `day14`,
    max(`dutyreport`.`day15`) AS `day15`,
    max(`dutyreport`.`day16`) AS `day16`,
    max(`dutyreport`.`day17`) AS `day17`,
    max(`dutyreport`.`day18`) AS `day18`,
    max(`dutyreport`.`day19`) AS `day19`,
    max(`dutyreport`.`day20`) AS `day20`,
    max(`dutyreport`.`day21`) AS `day21`,
    max(`dutyreport`.`day22`) AS `day22`,
    max(`dutyreport`.`day23`) AS `day23`,
    max(`dutyreport`.`day24`) AS `day24`,
    max(`dutyreport`.`day25`) AS `day25`,
    max(`dutyreport`.`day26`) AS `day26`,
    max(`dutyreport`.`day27`) AS `day27`,
    max(`dutyreport`.`day28`) AS `day28`,
    max(`dutyreport`.`day29`) AS `day29`,
    max(`dutyreport`.`day30`) AS `day30`,
    max(`dutyreport`.`day31`) AS `day31`
from (
        `dutyreport`
        join `ihrisdata` on(
            (
                `ihrisdata`.`ihris_pid` = `dutyreport`.`ihris_pid`
            )
        )
    )
group by `ihrisdata`.`ihris_pid`