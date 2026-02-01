jQuery(document).ready(function($) {
    $('.trigger-rule').on('click', function() {
        var button = $(this);
        var ruleId = button.data('rule-id');
        var clubId = button.data('club-id');
        var clubNumber = button.data('club-number');

        if (confirm('Are you sure you want to trigger this rule?')) {
            $.ajax({
                url: rules_trigger_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'trigger_rule',
                    rule_id: ruleId,
                    club_id: clubId,
                    club_number: clubNumber,
                    nonce: rules_trigger_params.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Rule triggered successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('AJAX error');
                }
            });
        }
    });

    $('.clear-rule').on('click', function() {
        var button = $(this);
        var ruleId = button.data('rule-id');
        var clubId = button.data('club-id');
        var clubNumber = button.data('club-number');
        
        if (confirm('Are you sure you want to clear one trigger for this rule?')) {
            $.ajax({
                url: rules_trigger_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'clear_rule',
                    rule_id: ruleId,
                    club_id: clubId,
                    club_number: clubNumber,
                    nonce: rules_trigger_params.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Trigger cleared successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('AJAX error');
                }
            });
        }
    });
});