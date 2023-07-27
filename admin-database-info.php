<?php
/*
Plugin Name: Database & table Info
Description: Display database & table information in the WordPress admin area. Also allows you to view the first 10 rows of any table in the database.
Version: 0.1
Author: <a href="https://www.linkedin.com/in/dennis-kjaer-christensen/">Dennis K. Christensen</a> | <a href="https://visualbusiness.dk">Visual Business</a>
*/

// Register the activation hook
register_activation_hook(__FILE__, 'admin_database_info_activation');

function admin_database_info_activation() {
    // Perform any activation tasks here, if needed.
}
// Add the admin menu page
add_action('admin_menu', 'admin_database_info_add_menu');

function admin_database_info_add_menu() {
    add_menu_page(
        'Database Info',
        'Database Info',
        'manage_options',
        'admin-database-info',
        'admin_database_info_page',
        'dashicons-database',
        75
    );
}
function admin_database_info_page() {
    global $wpdb;

    // Get some basic database information
    $database_name = $wpdb->dbname;
    $database_host = $wpdb->dbhost;
    $database_user = $wpdb->dbuser;
    $database_charset = $wpdb->charset;
    $database_collation = $wpdb->collate;

    // Get the list of all tables in the WordPress database
    $all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

    // Output the information in a simple table
    echo '<div class="wrap">';
    echo '<h1>Database Information</h1>';

    echo '<h2>Basic Information</h2>';
    echo '<table class="widefat">';
    echo '<tr><td>Database Name:</td><td>' . $database_name . '</td></tr>';
    echo '<tr><td>Database Host:</td><td>' . $database_host . '</td></tr>';
    echo '<tr><td>Database User:</td><td>' . $database_user . '</td></tr>';
    echo '<tr><td>Database Charset:</td><td>' . $database_charset . '</td></tr>';
    echo '<tr><td>Database Collation:</td><td>' . $database_collation . '</td></tr>';
    echo '</table>';

    echo '<h2>All Tables</h2>';
    echo '<table class="widefat">';
    echo '<tr><th>Table Name</th></tr>';
    foreach ($all_tables as $table) {
        $table_name = $table[0];
        echo '<tr><td><a href="#" class="table-link" data-table="' . $table_name . '">' . $table_name . '</a></td></tr>';
    }
    echo '</table>';

    echo '<div class="table-data-container"></div>';
    echo '</div>';

    // Enqueue the necessary JavaScript/jQuery code
    echo '<script>';
    echo 'jQuery(document).ready(function($) {';
    echo '    $(".table-link").on("click", function(e) {';
    echo '        e.preventDefault();';
    echo '        var tableName = $(this).data("table");';
    echo '        $.ajax({';
    echo '            url: "' . admin_url('admin-ajax.php') . '",';
    echo '            type: "post",';
    echo '            data: {';
    echo '                action: "get_table_data",';
    echo '                table: tableName';
    echo '            },';
    echo '            success: function(response) {';
    echo '                $(".table-data-container").html(response);';
    echo '            }';
    echo '        });';
    echo '    });';
    echo '});';
    echo '</script>';
}


add_action('wp_ajax_get_table_data', 'get_table_data_callback');
add_action('wp_ajax_nopriv_get_table_data', 'get_table_data_callback');

function get_table_data_callback() {
    if (isset($_POST['table'])) {
        global $wpdb;
        $table_name = sanitize_text_field($_POST['table']);
        
        // Get the first 10 rows from the selected table
        $table_data = $wpdb->get_results("SELECT * FROM $table_name LIMIT 10", ARRAY_A);
        
        if ($table_data) {
            echo '<h2>Table Data: ' . $table_name . '</h2>';
            echo '<table class="widefat">';
            echo '<tr>';
            foreach ($table_data[0] as $column_name => $value) {
                echo '<th>' . $column_name . '</th>';
            }
            echo '</tr>';
            foreach ($table_data as $row) {
                echo '<tr>';
                foreach ($row as $value) {
                    echo '<td>' . $value . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p>No data found for table: ' . $table_name . '</p>';
        }
    }
    wp_die();
}

