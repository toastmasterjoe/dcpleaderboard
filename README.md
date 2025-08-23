# Prerequisites
1. Perma links should not be set to plain for the plugin to work correctly.
2. You should ensure that you run wp-cron either through the servers cron process or through wordpress
3. Wordpress 6.6.1 or later and PHP 7.4

# Setup
1. Upload the plugin to your server
2. Go to Settings > DCP Leaderboard change the district to your districts number and save
2. Set permalink to postname and save
3. Ensure wp cron is running
4. Create a new page
5. Add the shortcode [districtleaderboard_content /]

# Troubleshooting
1. If you receive a 404 not found on syncing go on permalink settings and click save again to index the rest endpoint added by the plugin