<?php 

class PointsClubView {

public static function getDistrictClubPoints(int $club_id): array {
        global $wpdb;
        $ruleTriggers = $wpdb->get_results($wpdb->prepare("SELECT r.id, r.name, r.description, r.points, count(rt.point_rule_id) as trigger_count, COALESCE(COUNT(rt.point_rule_id) * r.points, 0) AS points_awarded  FROM  wp_points_rule r left join wp_points_rule_trigger rt on r.id = rt.point_rule_id AND rt.club_id=%d  group by r.id", $club_id), OBJECT);
        return $ruleTriggers;
    }
/*SELECT r.id, r.name, r.description, r.points, count(rt.point_rule_id) as trigger_count, COALESCE(COUNT(rt.point_rule_id) * r.points, 0) AS total_points  FROM  wp_points_rule r left join wp_points_rule_trigger rt on r.id = rt.point_rule_id AND rt.club_id=60  group by r.id;*/
}