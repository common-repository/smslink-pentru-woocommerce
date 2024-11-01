<?php 
/*
Plugin Name: SMSLink pentru WooCommerce
Plugin URI: http://www.smslink.ro
Description: Integrati fara efort magazinul dvs. online cu platforma SMSLink pentru a transmite notificari SMS catre clientii dvs., imbunatatind astfel comunicarea cu acestia.
Version: 1.0.3
Author: ASTINVEST COM SRL
Author URI: http://blog.smslink.ro
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: smslink
*/

$pluginDir = plugin_dir_path(__FILE__);
$pluginDirUrl = plugin_dir_url(__FILE__);

global $smslink_db_version;

$smslink_db_version = '1.0';

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) 
{
    return;
}

if (!class_exists('WP_List_Table')) 
{
    require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');
}

include 'LogListTable.php';

function smslink_install()
{
    global $wpdb;
    global $smslink_db_version;

    $table_name = $wpdb->prefix . 'smslink_log';
    $charset_collate = $wpdb->get_charset_collate();
    $installed_ver = get_option('smslink_db_version');

    if ($installed_ver != $smslink_db_version) 
    {
        $sql = "CREATE TABLE `$table_name` ( `id` BIGINT(20) NOT NULL AUTO_INCREMENT, `receiver` VARCHAR(50) NULL , `message` TEXT NULL , `timestamp_queued` datetime DEFAULT NULL , `timestamp_sent` datetime DEFAULT NULL , `remote_message_id` BIGINT(20) NOT NULL DEFAULT '0' , `remote_status` INT(2) NOT NULL DEFAULT '0' , `remote_response` TEXT NULL , PRIMARY KEY (`id`)) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('smslink_db_version', $smslink_db_version);
        
    }
    
}

register_activation_hook(__FILE__, 'smslink_install');

function smslink_update_db_check()
{
    global $smslink_db_version;
    
    if (get_site_option('smslink_db_version') != $smslink_db_version) 
    {
        
    }
    
}

add_action('plugins_loaded', 'smslink_update_db_check');

function smslink_load_scripts()
{
    if (!wp_script_is('jquery', 'enqueued')) 
    {
        wp_enqueue_script('jquery');
    }
       
}

add_action('admin_enqueue_scripts', 'smslink_load_scripts');

function smslink_optout($checkout)
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['optout'])) 
    {
        $optout = $options['optout'];
    } 
    else 
    {
        $optout = '';
    }
    
    if (!empty($optout)) 
    {
        echo '<div>';
        
        woocommerce_form_field(
            'smslink_optout', 
            array(
                'type' => 'checkbox',
                'class' => array('input-checkbox', 'form-row-wide'),
                'label' => __('&nbsp;Nu doresc sa primesc prin SMS informatii despre starea comenzii.', 'smslink'),
            ), 
            $checkout->get_value('smslink_optout')
        );
            
        echo '
           </div>
           <div style="clear: both">&nbsp;</div>
        ';
        
    }
    
}

add_action('woocommerce_after_order_notes', 'smslink_optout');

function smslink_optout_update_order_meta($orderId)
{
    if (isset($_POST['smslink_optout'])) 
    {
        update_post_meta($orderId, 'smslink_optout', esc_attr($_POST['smslink_optout']));
    }
    
}

add_action('woocommerce_checkout_update_order_meta', 'smslink_optout_update_order_meta');

add_action('admin_menu', 'smslink_add_menu');

function smslink_add_menu()
{
    add_menu_page(
        __('SMSLink', 'smslink'),
        __('SMSLink', 'smslink'),
        'manage_options',
        'smslink_main',
        'smslink_main',
        plugin_dir_url(__FILE__).'images/logo.png'
    );

    add_submenu_page(
        'smslink_main',
        __('Configurare si Sabloane', 'smslink'),
        __('Configurare si Sabloane', 'smslink'),
        'manage_options',
        'smslink_login',
        'smslink_login'
    );

    add_submenu_page(
        'smslink_main',
        __('Istoric si Cautare SMS', 'smslink'),
        __('Istoric si Cautare SMS', 'smslink'),
        'manage_options',
        'smslink_log',
        'smslink_log'
    );

    add_submenu_page(
        'smslink_main',
        __('Testare Configuratie', 'smslink'),
        __('Testare Configuratie', 'smslink'),
        'manage_options',
        'smslink_test',
        'smslink_test'
    );
    
}

function smslink_main()
{
    ?>
    <style>
    
        ul.SMSLinkList { margin: 0; padding: 0 0 0 10px; }
        ul.SMSLinkList li { margin: 0; padding: 0 0 0 10px; }
        ul.SMSLinkList li ul { margin: 0; padding: 5px 0 5px 10px; }
        ul.SMSLinkList li ul li { margin: 0; padding: 0 0 0 10px; }
        
        ul.WithBullets { list-style: disc; margin-left: 20px; }
        ul.WithBullets li { }
        
    </style>
    <div class="wrap">
        <table>
            <tr>
                <td><a href="http://www.smslink.ro/inregistrare/" target="_blank"><img src="<?php echo plugin_dir_url(__FILE__).'images/smslink-logo.png'; ?>" width="175" border="0" style="padding-right: 10px;" /></a></td>
                <td><h2><?=__('SMSLink<sup>&reg;</sup> pentru WooCommerce', 'smslink')?></h2></td>
            </tr>
            <tr>
                <td colspan="2"><h3><?=__('Avantajele notificarilor SMS pentru eCommerce', 'smslink')?></h3></td>
            </tr>
            <tr>
                <td colspan="2">
                    <ul class="SMSLinkList WithBullets">
                        <li><?=__('Notificarile SMS pentru informarea clientilor despre starea comenzilor reprezinta un must-have pentru orice magazin online.', 'smslink')?></li>
                        <li><?=__('In majoritatea domeniilor de activitate, numarul de comenzi anulate va scadea, clientul fiind intotdeauna informat despre starea comenzii prin SMS.', 'smslink')?></li>
                        <li><?=__('Construiti o relatie mai puternica prin informarea prin SMS a clientilor dvs.', 'smslink')?></li>
                        <li><?=__('Informand clientii prin SMS, increderea in magazinul dvs. online creste.', 'smslink')?></li>
                        <li><?=__('SMS-ul este cel mai personal mijloc de comunicare directa, fiind un canal citit in 99% din cazuri.', 'smslink')?></li>
                        <li>... <?=__('<a href="http://www.smslink.ro/" target="_blank">si multe alte avantaje aici</a>', 'smslink')?></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?=__('Cat costa serviciul SMS?', 'smslink')?></h3></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?=__('SMSLink tarifeaza doar SMS-urile transmise, astfel ca nu exista abonamente lunare sau taxe initiale. Puteti transmite 1 sau 100.000 SMS-uri intr-o luna.', 'smslink')?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <ul class="SMSLinkList WithBullets">
                        <li>
                            <?=__('<a href="http://www.smslink.ro/content.php?content_id=21&info=sms-gateway-tarife-de-utilizare" target="_blank">Vizualizati oferta comerciala completa aici</a>', 'smslink')?>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?=__('Cum pot utiliza SMSLink pentru WooCommerce?', 'smslink')?></h3></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?=__('Nimic mai simplu, odata ce ati instalat si activat modulul SMSLink pentru WooCommerce, trebuie sa parcurgeti urmatorii pasi, care va vor necesita atentia aproximativ 10 minute.', 'smslink')?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <ul class="SMSLinkList">
                        <li>1. <?=__('<a href="http://www.smslink.ro/inregistrare/" target="_blank">Creati un cont de utilizator</a> gratuit pe SMSLink. Daca aveti deja un cont de utilizator, <a href="http://www.smslink.ro/autentificare/" target="_blank">va puteti autentifica</a>.', 'smslink')?></li>
                        <li>2. <?=__('De pe Dashboard-ul din contul dvs. de utilizator, accesati <a href="http://www.smslink.ro/sms/gateway/setup.php" target="_blank">Configurare si setari</a> de la sectiunea SMS Gateway.', 'smslink')?></li>
                        <li>
                            3. <?=__('In pagina deschisa de pe SMSLink, la rubrica <b>Creare conexiune SMS Gateway</b>:', 'smslink')?>
                            <ul>
                                <li>3.1. <?=__('Introduceti o parola pe care va rugam sa o tineti minte cateva minute.', 'smslink')?></li>
                                <li>3.1. <?=__('Lasati restul datelor asa cum sunt precompletate si apasati <b>Salvare conexiune</b>.', 'smslink')?></li>
                            </ul>
                        </li>
                        <li>4. <?=__('Din contul dvs. de utilizator de pe SMSLink, <a href="http://www.smslink.ro/financial/prepaid/buy.php" target="_blank">alegeti un pachet de SMS-uri</a> astfel incat sa aveti SMS-uri disponibile in cont. ', 'smslink')?></li>
                        <li>
                            5. <?=__('In magazinul dvs. online accesati <b>Configurare si Sabloane</b> din tab-ul SMSLink', 'smslink')?>
                            <ul>
                                <li>5.1. <?=__('Introduceti Connection ID-ul generat mai devreme pe SMSLink. (Connection ID-ul este compus din litere si cifre, spre exemplu: A2557A82317C10)', 'smslink')?></li>
                                <li>5.2. <?=__('Introduceti Parola pe care ati memorat-o mai devreme, introdusa pe SMSLink la rubrica <b>Creare conexiune SMS Gateway</b>.', 'smslink')?></li>
                                <li>5.3. <?=__('Personalizati sabloanele si restul optiunilor dupa cum va doriti.', 'smslink')?></li>
                                <li>5.4. <?=__('Selectati Salvare.', 'smslink')?></li>
                            </ul>
                        </li>                        
                        <li>6. <?=__('Serviciul devine activ si functional.', 'smslink')?></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 10px 0 0 20px; font-size: 14px;">
                    <?=__('Nu va descurcati cu instalarea si configurarea?<br /><b>Beneficiati de support tehnic, gratuit, prin e-mail si telefon, 24/7/365</b> la <a href="http://www.smslink.ro/contact.php" target="_blank">coordonatele de contact de aici</a>.', 'smslink')?>
                </td>
            </tr>
            <tr>
                <td colspan="2"><h3><?=__('Despre SMSLink', 'smslink')?></h3></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?=__('Va prezentam mai jos cateva informatii pe care le consideram relevante despre noi, pentru a putea avea astfel o vedere de ansamblu.', 'smslink')?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <ul class="SMSLinkList WithBullets">
                        <li><?=__('Lansata in 2008 (de peste 9 ani), SMSLink este prima platforma SMS, disponibila imediat, din Romania.', 'smslink')?></li>
                        <li><?=__('Peste 700 de clienti din domenii ca banking, telecom, retail au ales serviciile noastre.', 'smslink')?></li>
                        <li><?=__('Transmitem anual peste 60 de milioane de SMS-uri, incepand din 2008.', 'smslink')?></li>    
                        <li><?=__('Furnizam servicii unificate de SMS Marketing, SMS Gateway, 2-Way SMS, Mail to SMS si SMS Alerts.', 'smslink')?></li>
                        <li><?=__('Furnizam support 24/7/365 prin e-mail si telefon, pentru toate serviciile SMS.', 'smslink')?></li>
                        <li><?=__('Suntem singurul furnizor de servicii SMS din Romania in clasamentul Deloitte Technology Fast 50, CE (Central Europe), 2015 (Locul 13)', 'smslink')?></li>
                        <li><?=__('Suntem singurul furnizor de servicii SMS din Romania in clasamentul Deloitte Technology Fast 500, EMEA (Europe, Middle East and Africa), 2015 (Locul 127)', 'smslink')?></li>
                    </ul>
                </td>
            </tr>
        </table>                
    </div>
    <?php
}

# options
add_action('admin_init', 'smslink_admin_init');
function smslink_admin_init()
{
    # for login
    register_setting(
        'smslink_plugin_options',
        'smslink_plugin_options',
        'smslink_plugin_options_validate'
    );
    add_settings_section(
        'smslink_plugin_login',
        __('Configurati datele de conectare pentru platforma SMSLink', 'smslink'),
        'smslink_plugin_login_section_text',
        'smslink_plugin'
    );
    add_settings_field(
        'smslink_plugin_options_connection_id',
        __('Connection ID', 'smslink'),
        'smslink_settings_display_connection_id',
        'smslink_plugin',
        'smslink_plugin_login'
    );
    add_settings_field(
        'smslink_plugin_options_password',
        __('Password', 'smslink'),
        'smslink_settings_display_password',
        'smslink_plugin',
        'smslink_plugin_login'
    );    
    add_settings_field(
        'smslink_plugin_options_testmode',
        __('Activare mod de teste', 'smslink'),
        'smslink_settings_display_testmode',
        'smslink_plugin',
        'smslink_plugin_login'
    );
    add_settings_field(
        'smslink_plugin_options_testmode_number',
        __('Numar telefon pentru teste', 'smslink'),
        'smslink_settings_display_testmode_number',
        'smslink_plugin',
        'smslink_plugin_login'
    );
    add_settings_field(
        'smslink_plugin_options_optout',
        __('Activeaza posibilitatea<br />de opt-out in cos', 'smslink'),
        'smslink_settings_display_optout',
        'smslink_plugin',
        'smslink_plugin_login'
    );
    add_settings_field(
        'smslink_plugin_options_content',
        __('Stare comenzi', 'smslink'),
        'smslink_settings_display_content',
        'smslink_plugin',
        'smslink_plugin_login'
    );
    add_settings_field(
        'smslink_plugin_options_enabled',
        __('', 'smslink'),
        'smslink_settings_display_enabled',
        'smslink_plugin',
        'smslink_plugin_login'
    );
}

function smslink_login()
{
    ?>
    <div class="wrap">
        <h2><?=__('SMSLink - Configurare si Sabloane', 'smslink')?></h2>
        <?php settings_errors(); ?>
        <form action="options.php" method="post">
            <?php settings_fields('smslink_plugin_options'); ?>
            <?php do_settings_sections('smslink_plugin'); ?>

            <input name="Submit" type="submit" class="button button-primary button-large" value="<?=__('Salvează', 'smslink')?>" />
        </form>
    </div>
    <?php
}

function smslink_test()
{
    if (isset($_POST) && !empty($_POST)) 
    {
        if (empty($_POST['smslink_phone'])) 
        {
            echo '<div class="notice notice-error is-dismissible">
                <p>'.__('Pentru a putea testa serviciul, va rugam introduceti numarul de telefon.', 'smslink').'</p>
            </div>';
        }
        
        if (empty($_POST['smslink_message'])) 
        {
            echo '<div class="notice notice-error is-dismissible">
                <p>'.__('Pentru a putea testa serviciul, va rugam introduceti textul SMS-ului.', 'smslink').'</p>
            </div>';
        }
        
        if (!empty($_POST['smslink_message']) && !empty($_POST['smslink_phone'])) 
        {
            $options = get_option('smslink_plugin_options');
            
            $connection_id = '';
            $password = '';
            
            if (!empty($options) && is_array($options) && isset($options['connection_id']))
                $connection_id = $options['connection_id'];
            
            if (!empty($options) && is_array($options) && isset($options['password'])) 
                $password = $options['password'];
            
            if (!empty($connection_id) && !empty($password)) 
            {
                $phone = smslink_phone($_POST['smslink_phone']);
                
                if (!empty($phone)) 
                {
                    $result = smslink_queue($connection_id, $password, $phone, $_POST['smslink_message']);
                    
                    $result_text = "SMS transmis, cu stare necunoscuta";
                    
                    switch ($result)

                    {

                        case 1:

                            $result_text = __('SMS transmis cu succes', 'smslink');

                            break;

                        case 2:

                            $result_text = __('Eroare (Date transmise incorecte, consultati istoricul SMS) la transmiterea SMS ', 'smslink');

                            break;

                        case 3:

                            $result_text = __('Eroare (Conexiune imposibila cu SMSLink, consultati istoricul SMS) la transmiterea SMS ', 'smslink');

                            break;

                    }

                    
                    echo '<div class="notice notice-success is-dismissible">
                              <p>'.$result_text.'</p>
                          </div>
                    ';
                    
                } 
                else 
                {
                    echo '<div class="notice notice-error is-dismissible">
                              <p>'.__('Numarul de telefon este lasat liber.', 'smslink').'</p>
                          </div>
                    ';
                    
                }
            
            } 
            else 
            {
                echo '<div class="notice notice-error is-dismissible">
                         <p>'.__('Modulul este neconfigurat. Va rugam accesati sectiunea Configurare si Sabloane.', 'smslink').'</p>
                     </div>
                ';
                
            }
            
        }
        
    }
    
    ?>
        <div class="wrap">
            <h2><?=__('SMSLink - Testare Configuratie', 'smslink')?></h2>
            <form method="post" action="<?=admin_url('admin.php?page=smslink_test')?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <b><?php echo __('Situatia conexiunii dintre magazinul dvs. online si contul dvs. de utilizator de pe SMSLink:', 'smslink'); ?></b><br />
                                <?php echo smslink_credit(); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?=__('Numar de telefon', 'smslink')?></th>
                            <td><input type="text" name="smslink_phone" style="width: 400px;" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><?=__('Mesaj', 'smslink')?></th>
                            <td>
                                <textarea name="smslink_message" class="smslink_content" style="width: 400px; height: 100px;" maxlength="160"><?php echo __('Acesta este un SMS pentru testarea serviciului SMSLink', 'smslink'); ?></textarea>
                                <p>160 <?=__('caractere ramase din 160 de caractere', 'smslink')?></p>
                            </td>
                        </tr>                        
                    </tbody>
                </table>
                <p style="clear: both;"><button type="submit" class="button button-primary button-large" id="smslink_queue_test"><?=__('Trimite SMS-ul', 'smslink')?></button></p>
            </form>
            <script type="text/javascript">
                var smslink_content = document.getElementsByClassName('smslink_content');
                for (var i = 0; i < smslink_content.length; i++) {
                    var smslink_element = smslink_content[i];
                    smslink_element.onkeyup = function() {
                        var text_length = this.value.length;
                        var text_remaining = 160 - text_length;
                        this.nextElementSibling.innerHTML = text_remaining + ' <?=__('caractere ramase din 160 de caractere', 'smslink')?>';
                    };
                }
            </script>
        </div>
    <?php
    
}

function smslink_log()
{
    ?>
    <div class="wrap">
        <h2><?=__('SMSLink - Istoric si Cautare SMS', 'smslink')?></h2>
        <form method="get">
            <?php
            
                $_table_list = new smslink_Log_List_Table();
                $_table_list->prepare_items();
                echo '<input type="hidden" name="page" value="smslink_log" />';
            
                $_table_list->views();
                $_table_list->search_box(__('Cauta dupa numarul de telefon', 'smslink' ), 'key');
                $_table_list->display();
            
            ?>
        </form>
    </div>
    <?php
}

function smslink_plugin_login_section_text()
{
    
}

function smslink_settings_display_connection_id()
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['connection_id'])) $connection_id = $options['connection_id'];
        else $connection_id = '';
    
    echo '
        <table>
            <tr>
                <td style="vertical-align: top; margin: 0; padding: 0;"><input id="smslink_settings_connection_id" name="smslink_plugin_options[connection_id]" type="text" value="'.$connection_id.'" style="width: 400px;" /></td>
                <td style="vertical-align: top; margin: 0; padding: 0;">
                    <a href="http://www.smslink.ro/inregistrare/" target="_blank"><img src="'.plugin_dir_url(__FILE__).'images/smslink-logo.png" width="175" border="0" style="float: left; padding-right: 10px;" /></a>
                </td>
                <td style="vertical-align: top; margin: 0; padding: 0;">
                    '.__('
                        Pentru a obtine datele de conectare in platforma SMSLink, <a href="http://www.smslink.ro/inregistrare/" target="_blank">inregistrati-va gratuit</a>, apoi accesati optiunea <a href="http://www.smslink.ro/sms/gateway/setup.php" target="_blank">SMS Gateway - Configurare si setari</a> pentru a genera Connection ID / Password-ul pentru conexiunea dintre SMSLink si magazinul dvs. online.
                        <b>Beneficiati de asistenta gratuita</b> pentru pentru instalarea, configurarea si utilizarea modulului SMSLink, prin e-mail sau telefon, <b>24/7/365</b>. Ne puteti contacta pentru asistenta la <a href="http://www.smslink.ro/contact.php" target="_blank">aceste coordonate de contact</a>.
                     ',
                     'smslink'
                    ).'                        
                </td>
            </tr>
        </table>    
    ';
    
}

function smslink_settings_display_password()
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['password'])) $password = $options['password'];
        else $password = '';

    echo '
        <table>
            <tr>
                <td style="vertical-align: top; margin: 0; padding: 0;"><input id="smslink_settings_password" name="smslink_plugin_options[password]" type="text" value="'.$password.'" style="width: 400px;" /></td>
                <td style="vertical-align: top; margin: 0; padding: 0 0 0 20px;">
                    '.__('<b>Atentie! </b> Valorile pentru Connection ID / Password nu reprezinta numele de utilizator si parola contului dvs. de utilizator de pe SMSLink, ci datele de la sectiunea <a href="http://www.smslink.ro/sms/gateway/setup.php" target="_blank">SMS Gateway - Configurare si setari</a>.', 'smslink').'
                </td>
            </tr>
        </table>    
    ';
    
}

function smslink_settings_display_testmode()
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['testmode'])) $testmode = $options['testmode'];
        else $testmode = '';

    echo '
        <table>
            <tr>
                <td style="vertical-align: top; margin: 0; padding: 5px 0 0 0;"><input id="smslink_settings_testmode" name="smslink_plugin_options[testmode]" type="checkbox" value="1" '.(!empty($testmode) ? 'checked="checked"' : '').' /></td>
                <td style="vertical-align: top; margin: 0; padding: 0 0 0 20px;">
                    '.__('Atunci cand modul de teste este activat, toate SMS-urile generate ca urmare a comenzilor se vor transmite catre numarul de telefon pentru teste, in locul numarului de telefon al clientului dvs. Acest lucru va ajuta sa vizualizati pe telefon mesajele transmise clientilor dvs.', 'smslink').'
                </td>
            </tr>
        </table>
    ';
    
}

function smslink_settings_display_testmode_number()
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['testmode_number'])) $number = $options['testmode_number'];
        else $number = '';

    echo '<input id="smslink_settings_testmode_number" name="smslink_plugin_options[testmode_number]" type="text" value="'.$number.'" style="width: 400px;" />  <span>Ex. 0720123456</span>';
    
}

function smslink_settings_display_optout()
{
    $options = get_option('smslink_plugin_options');
    
    if (!empty($options) && is_array($options) && isset($options['optout'])) $optout = $options['optout'];
        else $optout = '';

    echo '<input id="smslink_settings_optout" name="smslink_plugin_options[optout]" type="checkbox" value="1" '.(!empty($optout)?'checked="checked"':'').' />';
    
}

function smslink_settings_display_enabled()
{

}

function smslink_settings_display_content()
{
    echo '
        <table>
            <tr>
                <td colspan="2" style="font-weight: bold;">'.__('Stare comanda', 'smslink').'</td>
                <td style="font-weight: bold;">'.__('Text SMS', 'smslink').'</td>
                <td style="font-weight: bold;">'.__('Optiuni si variabile', 'smslink').'</td>
            </tr>
    ';
        
    $examples = array(
            'wc-pending'    => __('Comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], a fost plasata cu succes si urmeaza sa fie procesata. Info-Line: 0310001111', 'smslink'),
            'wc-processing' => __('Comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], este in curs de procesare si urmeaza a fi livrata. Info-Line: 0310001111', 'smslink'),
            'wc-on-hold'    => __('Comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], este in asteptare, deoarece unul sau mai multe produse lipsesc. Info-Line: 0310001111', 'smslink'),
            'wc-completed'  => __('Comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], a fost pregatita si va fi predata catre curier. Info-Line: 0310001111', 'smslink'),
            'wc-cancelled'  => __('Comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], a fost anulata. Info-Line: 0310001111', 'smslink'),
            'wc-refunded'   => __('Cererea de restituire pentru comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME], a fost finalizata. Info-Line: 0310001111', 'smslink'),
            'wc-failed'     => __('Exista o problema cu procesarea platii pentru comanda nr. {order_number}, in valoare de {order_total} lei, de pe [SITENAME]. Va rugam sa ne contactati. Info-Line: 0310001111', 'smslink')
        );

    foreach ($examples as $key => $example)
    {
        $examples[$key] = str_replace(
                                array(
                                    "[SITENAME]"
                                ), 
                                array(
                                    get_option('blogname')
                                ), 
                                $example
                            );

    }
    
    $variabiles = array(
            "{billing_first_name}", 
            "{billing_last_name}", 
            "{shipping_first_name}", 
            "{shipping_last_name}", 
            "{order_number}", 
            "{order_date}", 
            "{order_total}"
        );

    $variabilestext = array();
    foreach ($variabiles as $key => $variabile)
        $variabilestext[] = '<a href="#InsertVariabile" onclick="insertAtCaret(\'[TEXTFIELD]\', \''.$variabile.'\');">'.$variabile.'</a>';
                    
    $options = get_option('smslink_plugin_options');
    if (!empty($options) && is_array($options) && isset($options['content'])) 
    {
        $content = $options['content'];
        $enabled = $options['enabled'];
    } 
    else 
    {
        $content = array();
        $enabled = array();
    }
    
    $statuses = wc_get_order_statuses();
    
    foreach ($statuses as $key => $value) 
    {
        $checked = false;
        
        if (isset($enabled[$key])) 
            $checked = true;        
        
        echo '            
            <tr>
                <td valign="top" style="vertical-align: top; border-top: 1px #8C8C8C dotted;" nowrap><b>'.$value.'</b></td>
                <td valign="top" style="vertical-align: top; border-top: 1px #8C8C8C dotted;" nowrap><label><input type="checkbox" name="smslink_plugin_options[enabled]['.$key.']" value="1" '.($checked ? 'checked="checked"' : '').' /> Activ</label></td>
                <td valign="top" style="vertical-align: top; border-top: 1px #8C8C8C dotted;">
                    <textarea id="smslink_settings_content_'.$key.'" name="smslink_plugin_options[content]['.$key.']" style="width: 500px; height: 100px;" class="smslink_content">'.(isset($content[$key]) ? $content[$key] : '').'</textarea>
        ';

        if (160 - strlen($content[$key]) > 0) $MessageLength = (160 - strlen($content[$key])).' '.__('caractere disponibile din 160 caractere', 'smslink');
            else $MessageLength = __('<b>Ati depasit cu </b>', 'smslink').((-1) * (160 - strlen($content[$key]))).__(' caractere limita de 160 caractere', 'smslink');
                                
        echo '
                    <p>'.$MessageLength.'</p>
                </td>
                <td valign="top" style="margin: 0; padding: 0; vertical-align: top; border-top: 1px #8C8C8C dotted;">
                    <table cellpadding="0" cellspacing="0" border="0">                        
        ';
        
        if (isset($examples[$key]))
        {
            echo '<tr>
                       <td style="vertical-align: top;" nowrap>'.__('Exemplu: ', 'smslink').'</td>
                       <td style="vertical-align: top;">
                            '.$examples[$key].'<br />
                            <a href="#CopyExample" onclick="document.getElementById(\'smslink_settings_content_'.$key.'\').value = \''.$examples[$key].'\';">'.__('Utilizeaza acest exemplu', 'smslink').'</a>
                       </td> 
                  </tr>
            ';
        }

        echo '<tr>
                    <td style="vertical-align: top;" nowrap>'.__('Variabile disponibile: ', 'smslink').'</td>
                    <td style="vertical-align: top;">
                        '.str_replace('[TEXTFIELD]', 'smslink_settings_content_'.$key, implode(", ", $variabilestext)).'
                    </td>
              </tr>        
        ';
        
        
        echo '
                    </table>
                </td>
            </tr>                      
            <script type="text/javascript">
                var smslink_content = document.getElementsByClassName("smslink_content");
                for (var i = 0; i < smslink_content.length; i++) {
                    var smslink_element = smslink_content[i];
                    smslink_element.onkeyup = function() {
                        var text_length = this.value.length;
                        var text_remaining = 160 - text_length;
                        if (text_remaining > 0) this.nextElementSibling.innerHTML = text_remaining + " '.__('caractere disponibile din 160 caractere', 'smslink').'";
                            else this.nextElementSibling.innerHTML = "'.__('<b>Ati depasit cu </b>', 'smslink').'" + ((-1) * text_remaining) + " '.__('caractere limita de 160 caractere', 'smslink').'";
                    };
                }
            </script>
        ';
        
    }
    
    ?>
        <script type="text/javascript">
                
            function insertAtCaret(areaId, text) 
            {
            	var txtarea = document.getElementById(areaId);
            	if (!txtarea) { return; }

                var scrollPos = txtarea.scrollTop;
            	var strPos = 0;
            	var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
            		"ff" : (document.selection ? "ie" : false));

                if (br == "ie") 
            	{
            		txtarea.focus();
            		var range = document.selection.createRange();
            		range.moveStart ('character', -txtarea.value.length);
            		strPos = range.text.length;
            	} 
            	else if (br == "ff") 
            	{
            		strPos = txtarea.selectionStart;
            	}

                var front = (txtarea.value).substring(0, strPos);
            	var back = (txtarea.value).substring(strPos, txtarea.value.length);
            	txtarea.value = front + text + back;
            	strPos = strPos + text.length;
            
            	if (br == "ie") 
            	{
            		txtarea.focus();
            		var ieRange = document.selection.createRange();
            		ieRange.moveStart ('character', -txtarea.value.length);
            		ieRange.moveStart ('character', strPos);
            		ieRange.moveEnd ('character', 0);
            		ieRange.select();
            	} 
            	else if (br == "ff") 
            	{
            		txtarea.selectionStart = strPos;
            		txtarea.selectionEnd = strPos;
            		txtarea.focus();
            	}

                txtarea.scrollTop = scrollPos;
            }
        </script>            
    <?php 
    
    echo '</table>';
    
}

function smslink_plugin_options_validate($input)
{
    return $input;
}

add_action("woocommerce_order_status_changed", "smslink_order_status_changed");

function smslink_order_status_changed($order_id, $checkout = null)
{
    global $woocommerce;
    
    $order = new WC_Order($order_id);
    
    $order_handling = "variabile obtinute prin obiecte";
    if (smslink_woocommerce_version_check())
        $order_handling = "variabile obtinute prin functii";
    
    if (smslink_woocommerce_version_check())
    {
        $order_data = $order->get_data();
        $status = $order_data['status'];
    }
    else
    {
        $status = $order->status;
    }
    
    $order_meta = get_post_meta($order_id);
       
    if (isset($order_meta['smslink_optout'])) 
    {
        return;
    }
   
    $options = get_option('smslink_plugin_options');

    $content = array();

    $enabled = array();
    
    if (!empty($options) && is_array($options) && isset($options['content'])) 
    {
        $content = $options['content'];
        $enabled = $options['enabled'];
    } 
    
    $connection_id = '';
    if (!empty($options) && is_array($options) && isset($options['connection_id'])) 
        $connection_id = $options['connection_id'];
    
    $password = '';
    if (!empty($options) && is_array($options) && isset($options['password'])) 
        $password = $options['password'];
    
    if ((strlen($connection_id) > 0) && (strlen($password) > 0)) 
    {
        if (isset($content['wc-' . $status]) && !empty($content['wc-' . $status]) && isset($enabled['wc-'.$status])) 
        {
            $message = $content['wc-' . $status];

            if (smslink_woocommerce_version_check())
            {
                $replace = array(
                        '{billing_first_name}'     => smslink_message_text($order_data['billing']['first_name']),
                        '{billing_last_name}'      => smslink_message_text($order_data['billing']['last_name']),
                        '{shipping_first_name}'    => smslink_message_text($order_data['shipping']['first_name']),
                        '{shipping_last_name}'     => smslink_message_text($order_data['shipping']['last_name']),
                        '{order_number}'           => $order_id,
                        '{order_date}'             => $order_data['date_created']->date('d-m-Y'),
                        '{order_total}'            => $order->get_total()
                    );
            }
            else
            {
                $replace = array(
                        '{billing_first_name}'     => smslink_message_text($order->billing_first_name),
                        '{billing_last_name}'      => smslink_message_text($order->billing_last_name),
                        '{shipping_first_name}'    => smslink_message_text($order->shipping_first_name),
                        '{shipping_last_name}'     => smslink_message_text($order->shipping_last_name),
                        '{order_number}'           => $order_id,
                        '{order_date}'             => date('d-m-Y', strtotime($order->order_date)),
                        '{order_total}'            => $order->get_total()
                    );
            }
            
            foreach ($replace as $key => $value) 
                $message = str_replace($key, $value, $message);

            if (!empty($options) && is_array($options) && isset($options['content']) && isset($options['testmode']) && !empty($options['testmode_number'])) $phone = smslink_phone($options['testmode_number']);
               else $phone = smslink_phone($order->billing_phone);

            if (!empty($phone))
            {
                $result = smslink_queue($connection_id, $password, $phone, $message);
                
                $result_text = "SMS notificare actualizare stare comanda transmis, cu stare necunoscuta";

                switch ($result)
                {
                    case 1:
                        $result_text = __('SMS transmis cu succes,', 'smslink');
                        break;
                    case 2:
                        $result_text = __('Eroare (Date transmise incorecte, consultati istoricul SMS) la transmiterea SMS ', 'smslink');
                        break;
                    case 3:
                        $result_text = __('Eroare (Conexiune imposibila cu SMSLink, consultati istoricul SMS) la transmiterea SMS ', 'smslink');
                        break;
                }

                
                $order->add_order_note(__($result_text.' catre '.$phone.' ('.$order_handling.'): '.$message, 'smslink'));

            }
            
        }
        
    }
    
}

add_action('add_meta_boxes', 'smslink_order_details_meta_box');

function smslink_order_details_meta_box()
{
    add_meta_box(
        'smslink_meta_box',
        __('Trimite SMS', 'smslink'),
        'smslink_order_details_sms_box',
        'shop_order',
        'side',
        'high'
    );
    
}

function smslink_order_details_sms_box($post)
{
    ?>
        <input type="hidden" name="smslink_order_id" id="smslink_order_id" value="<?=$post->ID?>" />
        
        <p><?=__('Telefon:', 'smslink')?></p>
        <p><input type="text" name="smslink_phone" id="smslink_phone" style="width: 100%" /></p>
        
        <p><?=__('Mesaj:', 'smslink')?></p>
        <div>
            <textarea name="smslink_content" class="smslink_content" id="smslink_content" style="width: 100%; height: 100px;" maxlength="160"></textarea>
            <p>160 <?=__('caractere ramase din 160 de caractere', 'smslink')?></p>
        </div>
        
        <p><button type="submit" class="button" id="smslink_queue_single"><?=__('Trimite SMS-ul', 'smslink')?></button></p>
        <script type="text/javascript">

            var smslink_content = document.getElementsByClassName("smslink_content");

            for (var i = 0; i < smslink_content.length; i++) 
            {
                var smslink_element = smslink_content[i];
                smslink_element.onkeyup = function() {
                    var text_length = this.value.length;
                    var text_remaining = 160 - text_length;
                    this.nextElementSibling.innerHTML = text_remaining + " <?=__('caractere ramase din 160 de caractere', 'smslink')?>";
                };
            }
            
        </script>
    <?php
}

function smslink_javascript_send_single() 
{ 
    ?>
    	<script type="text/javascript" >
        	jQuery(document).ready(function($) {
        	    jQuery('#smslink_queue_single').on('click', function() {
        	        jQuery('#smslink_queue_single').html('<?=__('Se trimite...', 'smslink')?>');
        	        jQuery('#smslink_queue_single').attr('disabled', 'disabled');
        	        var data = {
                        'action': 'smslink_single',
                        'phone': jQuery('#smslink_phone').val(),
                        'content': jQuery('#smslink_content').val(),
                        'order': jQuery('#smslink_order_id').val()
                    };
        
                    jQuery.post(ajaxurl, data, function(response) {
                        jQuery('#smslink_queue_single').html('<?=__('Trimite mesajul', 'smslink')?>');
        	            jQuery('#smslink_queue_single').removeAttr('disabled');
        	            jQuery('#smslink_phone').val('');
        	            jQuery('#smslink_content').val('');
        	            alert(response);
                    });
                });
        	});
    	</script> 
	<?php
}

add_action('admin_footer', 'smslink_javascript_send_single');

function smslink_ajax_send_single() 
{
    if (!empty($_POST['content']) && !empty($_POST['phone']) && !empty($_POST['order'])) 
    {
        $options = get_option('smslink_plugin_options');

        $connection_id = '';
        $password = '';
        
        if (!empty($options) && is_array($options) && isset($options['connection_id'])) 
        {
            $connection_id = $options['connection_id'];
        } 
        else 
        {
            echo __('Modulul SMSLink nu are setat Connection ID. Va rugam consultati sectiunea Configurare si Sabloane.', 'smslink');
            wp_die();
        }
        
        if (!empty($options) && is_array($options) && isset($options['password'])) 
        {
            $password = $options['password'];
        } 
        else 
        {
            echo __('Modulul SMSLink nu are setata Parola. Va rugam consultati sectiunea Configurare si Sabloane.', 'smslink');
            wp_die();
        }
                
        $phone = smslink_phone($_POST['phone']);
        
        if (!empty($phone)) 
        {
            $result = smslink_queue($connection_id, $password, $phone, $_POST['content']);
            
            $result_text = "SMS transmis, cu stare necunoscuta";
            
            switch ($result)
            {
                case 1:
                    $result_text = __('SMS transmis cu succes,', 'smslink');
                    break;
                case 2:
                    $result_text = __('Eroare (Date transmise incorecte, consultati istoricul SMS) la transmiterea SMS ', 'smslink');
                    break;
                case 3:
                    $result_text = __('Eroare (Conexiune imposibila cu SMSLink, consultati istoricul SMS) la transmiterea SMS ', 'smslink');
                    break;
            }
            
            global $woocommerce;
            
            $order = new WC_Order($_POST['order']);
            $order->add_order_note(__($result_text.', manual, catre '.$phone.': '.$_POST['content'], 'smslink'));
            
        }

        echo __('Mesajul a fost trimis catre '.$phone.".", 'smslink');     
           
    } 
    else 
    {
        echo __('Trebuie sa completati un numar de telefon pentru a putea transmite SMS-ul.', 'smslink');
        
    }
    
	wp_die();
	
}

add_action('wp_ajax_smslink_single', 'smslink_ajax_send_single');

function smslink_credit($TestConnection = 1)
{
    $options = get_option('smslink_plugin_options');

    $connection_id = '';
    $password = '';
    
    if (!empty($options) && is_array($options) && isset($options['connection_id']))
        $connection_id = $options['connection_id'];
    
    if (!empty($options) && is_array($options) && isset($options['password'])) 
        $password = $options['password'];

    if ((strlen($connection_id) == 0) or (strlen($password) == 0))
    {
        $Result = __('Datele de autentificare pentru platforma SMSLink <b>nu sunt configurate</b>. Va rugam consultati sectiunea Configurare si Sabloane pentru modulul SMSLink.', 'smslink');

    }
    else
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $URL = 'http://www.smslink.ro/sms/gateway/communicate/index.php?connection_id='.$connection_id.'&password='.$password.'&mode=credit&version=WOO_1.0.1';

        curl_setopt($ch, CURLOPT_URL, $URL);

        if (strpos($URL, "https://") !== false)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $Response = curl_exec($ch);

        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);      

        if ($curl_errno == 0)
        {
            $Response = explode(";", $Response);

            if ($Response[0] == "MESSAGE")
            {
                $Variabiles = explode(",", $Response[3]);

                if ($TestConnection == 1) $Result = __('Conexiune realizata cu succes. Mai aveti disponibile ', 'smslink').$Variabiles[0].__(' SMS la data de ', 'smslink').date("d-m-Y H:i:s", $Variabiles[1]).".";
                    else $Result = __('Mai aveti ', 'smslink').$Variabiles[0].__(' SMS la ', 'smslink').date("d-m-Y H:i:s", $Variabiles[1]).".";                

            }
            else
            {
                $Result = __('Datele de autentificare pentru platforma SMSLink <b>sunt incorecte</b>. Va rugam consultati sectiunea Configurare si Sabloane pentru modulul SMSLink.', 'smslink');;

            }
             
        }
        else
        {
            $Result = __('Creditul nu a putut fi interogat, va rugam incercati mai tarziu.', 'smslink');

        }

    }

    return $Result;

}

function smslink_queue($connection_id, $password, $phone, $message)
{
    global $wpdb;
    
    $ch = curl_init();
  
    $TimestampQueued = date("Y-m-d H:i:s");  

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  

    $URL = 'http://www.smslink.ro/sms/gateway/communicate/index.php?connection_id='.$connection_id.'&password='.$password.'&to='.$phone.'&message='.urlencode($message).'&version=WOO_1.0.1';   

    curl_setopt($ch, CURLOPT_URL, $URL);    

    if (strpos($URL, "https://") !== false)
    {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  

    $TimestampSent = date("Y-m-d H:i:s");   

    $RemoteStatus = 0;
    $RemoteMessageID = 0;   

    $RemoteResponse = array(
            "Response"   => curl_exec($ch),
            "Parsed"     => "",
            "Variabiles" => NULL
        );  

    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);

    if ($curl_errno == 0)
    {
        $RemoteResponse["Parsed"] = explode(";", $RemoteResponse["Response"]);  

        if ($RemoteResponse["Parsed"][0] == "MESSAGE") $RemoteStatus = 1;
            else $RemoteStatus = 2;   

        if ($RemoteStatus == 1)
        {
            $RemoteResponse["Variabiles"] = explode(",", $RemoteResponse["Parsed"][3]);
            $RemoteMessageID = $RemoteResponse["Variabiles"][0];
        }   

    }
    else
    {
        $RemoteStatus = 3;
        $RemoteResponse["Response"] = "CONNECTION ERROR;".$curl_errno.";".$curl_error;
    }

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO `".$wpdb->prefix."smslink_log` (`receiver`, `message`, `timestamp_queued`, `timestamp_sent`, `remote_message_id`, `remote_status`, `remote_response`) values (%s, %s, %s, %s, %s, %s, %s)",
            $phone,
            $message,
            $TimestampQueued,
            $TimestampSent,
            (int) $RemoteMessageID,
            (int) $RemoteStatus,
            $RemoteResponse["Response"]
        )
    );

    return $RemoteStatus;
 
}

function smslink_phone($Phone)
{
    $Phone = preg_replace('/\D/', '', $Phone);
        
    if (substr($Phone, 0, 4) == "0040")
        $Phone = substr($Phone, 3);
    
    if (substr($Phone, 0, 2) == "40")
        $Phone = substr($Phone, 1);
    
    if (strlen($Phone) < 10)
        if ($Phone[0] == "7")
            $Phone = "0".$Phone;
              
    return $Phone;
    
}

function smslink_message_text($Text)
{
    $Find = array("\xC4\x82", "\xC4\x83", "\xC3\x82", "\xC3\xA2", "\xC3\x8E", "\xC3\xAE", "\xC8\x98", "\xC8\x99", "\xC8\x9A", "\xC8\x9B", "\xC5\x9E", "\xC5\x9F", "\xC5\xA2", "\xC5\xA3", "\xC3\xA3", "\xC2\xAD", "\xe2\x80\x93");
    $Replace = array("A", "a", "A", "a", "I", "i", "S", "s", "T", "t", "S", "s", "T", "t", "a", " ", "-");
    
    $Text = str_replace($Find, $Replace, $Text);
    $Text = trim($Text);
    
    return $Text;
    
}

function smslink_woocommerce_version_check($version = '3.0')
{
    if (class_exists('WooCommerce'))
    {
        global $woocommerce;

        if (version_compare($woocommerce->version, $version, ">="))
        {
            return true;
        }

    }

    return false;

}