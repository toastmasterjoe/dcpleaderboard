<?php
/*
 * DCP Leaderboard Plugin
 * Copyright (C) 2025 Joseph Galea
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly to prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once plugin_dir_path( __FILE__ ) . '/options_page_register.php'; 
require_once plugin_dir_path( __FILE__ ) . '/../district-point-engine/point-rule.php'; 
require_once plugin_dir_path( __FILE__ ) . '/../district-point-engine/point-rule-triggers.php'; 
require_once plugin_dir_path( __FILE__ ) . '/../district-point-engine/points-engine.php'; 
require_once plugin_dir_path( __FILE__ ) . '/../clubs.php'; 

add_action('wp_ajax_trigger_rule', 'dcpleaderboard_trigger_rule');
add_action('wp_ajax_clear_rule', 'dcpleaderboard_clear_rule');

function dcpleaderboard_trigger_rule() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    check_ajax_referer('trigger_rule_nonce', 'nonce');

    $rule_id = intval($_POST['rule_id']);
    $club_id = intval($_POST['club_id']);
    $club_number = sanitize_text_field($_POST['club_number']);

    if (!$rule_id || !$club_id || !$club_number) {
        wp_send_json_error('Invalid data');
    }

    $pointRule = new PointRule();
    $rule = $pointRule->getRuleById($rule_id);

    if (!$rule || $rule->isAutomatic()) {
        wp_send_json_error('Cannot trigger automatic rule');
    }

    $triggerRecord = new PointRuleTriggerRecord($rule_id, $club_id, $club_number);
    // Check if already triggered if not multi_award
    if (!$rule->isMultiAward()) {
        if ($triggerRecord->getTriggerCount() > 0) {
            wp_send_json_error('Rule already triggered for this club');
        }
    }

    // Insert trigger
    $result = $triggerRecord->createTrigger();

    // Recalculate points for the club
    $clubData = new Clubs();
    $clubDataArray = $clubData->get_club_by_number($club_number);
    if (!$clubDataArray) {
        wp_send_json_error('Club not found for ' . $club_number);
    }
    // Recalculate points for the club
    $clubPoints = PointsEngine::reCalculatePoints([$clubDataArray]);

    $clubsDriver = new Clubs();
    $clubsDriver->update_club_district_points($clubPoints);

    if ($result > 0) {
        wp_send_json_success('Rule triggered successfully');
    } else {
        wp_send_json_error('Failed to trigger rule');
    }
}

function dcpleaderboard_clear_rule() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    check_ajax_referer('trigger_rule_nonce', 'nonce');

    $rule_id = intval($_POST['rule_id']);
    $club_id = intval($_POST['club_id']);
    $club_number = sanitize_text_field($_POST['club_number']);

    if (!$rule_id || !$club_id) {
        wp_send_json_error('Invalid data');
    }

    $pointRule = new PointRule();
    $rule = $pointRule->getRuleById($rule_id);

    if (!$rule || $rule->isAutomatic()) {
        wp_send_json_error('Cannot clear automatic rule');
    }
    
    // Delete one trigger 
    $triggerRecord = new PointRuleTriggerRecord($rule_id, $club_id, $club_number);
    $result = $triggerRecord->deleteTrigger();

    $clubData = new Clubs();
    $clubDataArray = $clubData->get_club_by_number($club_number);
    if (!$clubDataArray) {
        wp_send_json_error('Club not found for ' . $club_number);
    }
    // Recalculate points for the club
    $clubPoints = PointsEngine::reCalculatePoints([$clubDataArray]);
    $clubsDriver = new Clubs();
    $clubsDriver->update_club_district_points($clubPoints);

    if ($result !== false) {
        wp_send_json_success('Trigger cleared successfully');
    } else {
        wp_send_json_error('Failed to clear trigger');
    }
}

function render_dcp_leaderboard_rules_view_admin(){
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    $club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
    $club_number = isset($_GET['club_number']) ? sanitize_text_field($_GET['club_number']) : '';
    $title = 'Rules View';
    if ($club_id) {
        $clubDriver = new Clubs();
        $club = $clubDriver->get_club_by_id($club_id);
        $club_name = $club ? $club['club_name'] : 'Unknown Club';
        $title = 'Rules for Club: ' . esc_html($club_name);
    }
    echo '<div class="wrap"><h1>' . esc_html($title) . '</h1>';

    if ($club_id) {
        wp_enqueue_script('rules-trigger-script', plugin_dir_url(__FILE__) . 'js/rules-trigger.js', array('jquery'), null, true);
        wp_localize_script('rules-trigger-script', 'rules_trigger_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('trigger_rule_nonce'),
            'club_id' => $club_id
        ));
    }

    $table = new Rules_View_Table($club_id, $club_number);
    $table->prepare_items();

    echo '<form method="post">';
    if (!$club_id) {
        $table->search_box('Search Rules', 'rule_search');
    }
    $table->display();
    echo '</form>';

    echo '</div>';
}

class Rules_View_Table extends WP_List_Table {
    private $club_id;
    private $club_number;

    public function __construct($club_id = 0, $club_number = '') {
        $this->club_id = $club_id;
        $this->club_number = $club_number;
        parent::__construct([
            'singular' => 'rule',
            'plural'   => 'rules',
            'ajax'     => true
        ]);
    }

    public function get_columns() {
        $columns = [
            'cb'    => '<input type="checkbox" />',
            'id'  => 'Id',
            'name' => 'Name',
            'description' => 'Description',
            'points' => 'Points',
            'automatic' => 'Automatic',
            'multi_award' => 'Multi Award'
        ];
        if ($this->club_id) {
            $columns['triggers'] = 'Triggers';
            $columns['actions'] = 'Actions';
        }
        return $columns;
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="rule[]" value="%s" />', $item['id']);
    }

    public function column_default($item, $column_name) {
        if ($column_name == 'automatic' || $column_name == 'multi_award') {
            return $item[$column_name] ? 'Yes' : 'No';
        }
        return $item[$column_name];
    }

    public function get_bulk_actions() {
        /*return [
            'delete' => 'Delete'
        ];*/
    }

    public function process_bulk_action() {
        // Implement bulk actions if needed
    }

    public function prepare_items() {
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $pointRuleDriver = new PointRule();
        $all_rules = $pointRuleDriver->getAllRules();

        // Filter by search
        if (!empty($search)) {
            $all_rules = array_filter($all_rules, function($rule) use ($search) {
                return stripos($rule->getName(), $search) !== false || stripos($rule->getDescription(), $search) !== false;
            });
        }

        $total_rules = count($all_rules);

        // Paginate
        $offset = ($current_page - 1) * $per_page;
        $rules = array_slice($all_rules, $offset, $per_page);

        $data = [];
        $trigger_counts = [];
        if ($this->club_id) {
           $trigger_counts = PointRuleTriggerRecord::getTriggersByClubNumber($this->club_id);
        }
        foreach ($rules as $rule) {
            $item = [
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'description' => $rule->getDescription(),
                'points' => $rule->getPoints(),
                'automatic' => $rule->isAutomatic(),
                'multi_award' => $rule->isMultiAward()
            ];
            if ($this->club_id) {
                $count = isset($trigger_counts[$rule->getId()]) ? $trigger_counts[$rule->getId()] : 0;
                $item['triggers'] = $count;
                $actions = '';
                if (!$rule->isAutomatic()) {
                    if ($rule->isMultiAward() || $count == 0) {
                        $actions .= '<button type="button" class="button trigger-rule" data-rule-id="' . $rule->getId() . '" data-club-id="' . $this->club_id . '" data-club-number="' . esc_attr($this->club_number) . '">Trigger</button> ';
                    }
                    if ($count > 0) {
                        $actions .= '<button type="button" class="button clear-rule" data-rule-id="' . $rule->getId() . '" data-club-id="' . $this->club_id . '" data-club-number="' . esc_attr($this->club_number) . '">Clear Trigger</button>';
                    }
                }
                $item['actions'] = $actions;
            }
            $data[] = $item;
        }

        $this->items = $data;
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->set_pagination_args([
            'total_items' => $total_rules,
            'per_page'    => $per_page
        ]);

        $this->process_bulk_action();
    }
}
?>