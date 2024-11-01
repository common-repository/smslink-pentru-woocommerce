<?php

class smslink_Log_List_Table extends WP_List_Table 
{
    public function get_columns() 
    {
        return array(
                'id'                => __('ID', 'smslink'),
                'receiver'          => __('Telefon', 'smslink'),
                'message'           => __('Text', 'smslink'),
                'timestamp_queued'  => __('Data / Ora', 'smslink'),
                'remote_status'     => __('Stare', 'smslink'),
                'remote_message_id' => __('Detalii', 'smslink'),
                'remote_response'   => __('Log', 'smslink')
            );
    }

    public function column_cb($issue) 
    {
        return sprintf( '<input type="checkbox" name="smslink_log[]" value="%1$s" />', $issue['id'] );
    }

    public function column_id($issue) 
    {
        return $issue['id'];
    }

    public function column_receiver($issue) 
    {
        return $issue['receiver'];
    }

    public function column_message($issue) 
    {
        return $issue['message'];
    }

    public function column_timestamp_queued($issue) 
    {
        return $issue['timestamp_queued'];
    }

    public function column_remote_status($issue) 
    {
        switch ($issue["remote_status"])
        {
            case 1:
                return "Transmis catre SMSLink";
                break;
            case 2:
                return "Eroare returnata de SMSLink";
                break;
            case 3:
                return "Eroare conexiune";
                break;
            default:
                return "-";
                break;
        }
        
    }

    public function column_remote_message_id($issue)
    {
        $remote_message_id = $issue["remote_message_id"];
        
        if (($remote_message_id > 0) and (strlen($remote_message_id) > 0)) return '<a href="http://www.smslink.ro/sms/history-sent.php?message_id='.$remote_message_id.'" target="_blank">Deschide SMSLink</a>';
            else return '-';
    
    }
    
    public function column_remote_response($issue)
    {
        $remote_response = $issue["remote_response"];
        
        $ElementTempID = rand(1000000, 9999999);
    
        if (strlen($remote_response) > 0)
        {
            return '<a href="#Details" onclick="document.getElementById(\'Details'.$ElementTempID.'\').innerHTML = \''.addslashes($remote_response).'\';" style="text-decoration: ;">(+) Detalii</a> <div id="Details'.$ElementTempID.'" name="Details'.$ElementTempID.'"></div>';
        }
        else
        {
            return '-';
        }
    
    }
    
    protected function get_bulk_actions()
    {
        return array(

        );
        
    }

    public function get_sortable_columns()
    {
        return array(
            'id'               => array('id', true),
            'receiver'         => array('receiver', true),
            'timestamp_queued' => array('timestamp_queued', true),
            'remote_status'    => array('remote_status', true)
        );
        
    }

    public function prepare_items() 
    {
        global $wpdb;

        $per_page = 10;
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
        
        $table_name = $wpdb->prefix . 'smslink_log';

        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        if (1 < $current_page) $offset = $per_page * ( $current_page - 1 );
            else $offset = 0;

        $search = '';
        if (!empty($_REQUEST['s'])) 
            $search = "AND `receiver` LIKE '%" . esc_sql($wpdb->esc_like($_REQUEST['s'])) . "%' ";

        if (isset($_GET['orderby']) && isset($columns[$_GET['orderby']])) 
        {
            $orderBy = $_GET['orderby'];
            
            if (isset($_GET['order']) && in_array(strtolower($_GET['order']), array('asc', 'desc'))) $order = $_GET['order'];
                else $order = 'ASC';
                
        } 
        else 
        {
            $orderBy = 'id';
            $order = 'DESC';
        }

        $items = $wpdb->get_results(
                "SELECT * FROM $table_name WHERE 1 = 1 {$search}" .
                $wpdb->prepare("ORDER BY `$orderBy` $order LIMIT %d OFFSET %d;", $per_page, $offset), ARRAY_A
            );

        $count = $wpdb->get_var("SELECT COUNT(id) FROM $table_name WHERE 1 = 1 {$search};");

        $this->items = $items;

        $this->set_pagination_args(
            array(
                'total_items' => $count,
                'per_page'    => $per_page,
                'total_pages' => ceil( $count / $per_page )
                )
            );
        
    }
    
}
