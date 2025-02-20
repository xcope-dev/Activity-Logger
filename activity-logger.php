<?php
/*
Plugin Name: Activity Logger for Store
Description: Rejestruje aktywność (tworzenie i edycja wpisów oraz produktów) wykonywaną przez administratorów oraz kierowników sklepu. Zawiera możliwość czyszczenia starych logów oraz usuwania pozostałości przy dezinstalacji.
Version: 1.2
Author: Xcope
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Bezpośredni dostęp do pliku jest zabroniony.
}

class Activity_Logger {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'activity_log';

        // Rejestracja hooka aktywacyjnego wtyczki.
        register_activation_hook( __FILE__, array( $this, 'install' ) );

        // Logowanie zdarzeń przy zapisie wpisu (post, produkt, itd.).
        add_action( 'save_post', array( $this, 'log_post_activity' ), 10, 3 );

        // Dodanie strony z logami do panelu administracyjnego.
        add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
    }

    /**
     * Funkcja wywoływana przy aktywacji wtyczki.
     * Tworzy tabelę do rejestrowania logów.
     */
    public function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            user_role VARCHAR(50) NOT NULL,
            event_type VARCHAR(20) NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            post_type VARCHAR(50) NOT NULL,
            post_title VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            event_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Funkcja logująca aktywność przy zapisie wpisu.
     *
     * @param int     $post_ID ID wpisu.
     * @param WP_Post $post Obiekt wpisu.
     * @param bool    $update Czy wpis jest aktualizowany (true) czy tworzony (false).
     */
    public function log_post_activity( $post_ID, $post, $update ) {
        // Ignoruj autosave oraz rewizje.
        if ( wp_is_post_autosave( $post_ID ) || wp_is_post_revision( $post_ID ) ) {
            return;
        }

        // Logujemy tylko działania użytkowników z rolami administratora lub kierownika sklepu.
        $current_user = wp_get_current_user();
        if ( ! in_array( 'administrator', $current_user->roles ) && ! in_array( 'shop_manager', $current_user->roles ) ) {
            return;
        }

        global $wpdb;

        $event_type = $update ? 'updated' : 'created';
        $user_id    = $current_user->ID;
        $user_role  = implode( ', ', $current_user->roles );
        $post_type  = $post->post_type;
        $post_title = $post->post_title;
        $ip_address = $this->get_user_ip();

        $wpdb->insert(
            $this->table_name,
            array(
                'user_id'    => $user_id,
                'user_role'  => $user_role,
                'event_type' => $event_type,
                'post_id'    => $post_ID,
                'post_type'  => $post_type,
                'post_title' => $post_title,
                'ip_address' => $ip_address,
                'event_time' => current_time( 'mysql' )
            ),
            array(
                '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
            )
        );
    }

    /**
     * Pobiera adres IP użytkownika.
     *
     * @return string Adres IP.
     */
    private function get_user_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field( $ip );
    }

    /**
     * Dodaje stronę z logami do menu administracyjnego.
     */
    public function add_admin_page() {
        add_menu_page(
            'Log aktywności',
            'Log aktywności',
            'manage_options',
            'activity-log',
            array( $this, 'admin_page_html' ),
            'dashicons-visibility',
            80
        );
    }

    /**
     * Czyści logi starsze niż określona liczba dni.
     *
     * @param int $days Liczba dni, starsze logi zostaną usunięte.
     */
    public function clear_old_logs( $days ) {
        global $wpdb;
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE event_time < %s", $cutoff_date ) );
    }

    /**
     * Wyświetla stronę z logami aktywności wraz z opcją czyszczenia starych logów.
     */
    public function admin_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Obsługa formularza czyszczenia logów.
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'clear_logs' ) {
            check_admin_referer( 'clear_logs_action', 'clear_logs_nonce' );
            $days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;
            if ( $days <= 0 ) {
                $days = 30;
            }
            $this->clear_old_logs( $days );
            echo '<div class="updated"><p>Logi starsze niż ' . esc_html( $days ) . ' dni zostały usunięte.</p></div>';
        }

        global $wpdb;
        $logs = $wpdb->get_results( "SELECT * FROM {$this->table_name} ORDER BY event_time DESC" );
        ?>
        <div class="wrap">
            <h1>Log aktywności</h1>
            <form method="post" style="margin-bottom:20px;">
                <?php wp_nonce_field( 'clear_logs_action', 'clear_logs_nonce' ); ?>
                <input type="hidden" name="action" value="clear_logs">
                <label for="days">Wyczyść logi starsze niż (dni): </label>
                <input type="number" name="days" id="days" value="30" min="1" style="width:70px;">
                <?php submit_button( 'Wyczyść logi', 'secondary', 'submit', false ); ?>
            </form>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data i godzina</th>
                        <th>Użytkownik</th>
                        <th>Rola</th>
                        <th>Typ zdarzenia</th>
                        <th>Typ wpisu</th>
                        <th>Tytuł wpisu</th>
                        <th>Adres IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $logs ) : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log->id ); ?></td>
                                <td><?php echo esc_html( $log->event_time ); ?></td>
                                <td>
                                    <?php 
                                    $user_info = get_userdata( $log->user_id );
                                    echo $user_info ? esc_html( $user_info->user_login ) : 'Nieznany użytkownik';
                                    ?>
                                </td>
                                <td><?php echo esc_html( $log->user_role ); ?></td>
                                <td><?php echo esc_html( ucfirst( $log->event_type ) ); ?></td>
                                <td><?php echo esc_html( ucfirst( $log->post_type ) ); ?></td>
                                <td>
                                    <?php 
                                    if ( in_array( $log->post_type, array( 'post', 'product' ) ) ) {
                                        $edit_link = get_edit_post_link( $log->post_id );
                                        if ( $edit_link ) {
                                            echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $log->post_title ) . '</a>';
                                        } else {
                                            echo esc_html( $log->post_title );
                                        }
                                    } else {
                                        echo esc_html( $log->post_title );
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html( $log->ip_address ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="8">Brak logów do wyświetlenia.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

new Activity_Logger();

/**
 * Rejestracja hooka dezinstalacyjnego.
 * Przy dezinstalowaniu wtyczki zostanie usunięta tabela logów.
 */
register_uninstall_hook( __FILE__, 'activity_logger_uninstall' );
function activity_logger_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'activity_log';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}
