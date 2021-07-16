<?php
add_action('init', 'jobs_sitemap');
function jobs_sitemap(){
    add_feed('jobs-sitemap', 'create_jobs_sitemap');
}
 
function create_jobs_sitemap (){
    $charset = get_option( 'blog_charset' );
    header('Content-Type: '.feed_content_type('rss-http').'; charset='. $charset, true);
    $mainJobURL = get_option('PS_career_mainjoburl');
    
    global $wpdb;
    $jobList = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status != -1");
    
    echo '<?xml version="1.0" encoding="'. $charset.'"?'.'>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    foreach ($jobList as $job) { 
        echo '<url><loc>'.$mainJobURL.'?sh='.$job->shorthandle.'</loc><lastmod>'.$job->published_at.'</lastmod></url>';
    }
    
    echo '</urlset>';
}
?>