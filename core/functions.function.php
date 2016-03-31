<?php
/*
 * Create Custom Table API
 */

//define columns and column format
if( !function_exists( 'fortyfourwp_data_columns' ) ){
    function fortyfourwp_data_columns(){
        return array(
            'id'                    => '%d',
            'url'                   => '%s',
            'referrer'              => '%s',
            'alternative_keyword'   => '%s',
            'redirect_url'          => '%s',
            'redirect_type'         => '%s',
            'ip'                    => '%s',
            'access_date'           => '%s',
            'user_agent'            => '%s'
        );
    }
}

/**
* Inserts a data into the database
*
*@param $data array An array of key => value pairs to be inserted
*@return int The ID of the created data. Or WP_Error or false on failure.
*/
if( !function_exists( 'fortyfourwp_insert_data' ) ){
    function fortyfourwp_insert_data( $data=array() ){
        global $wpdb;

        //Set default values
        $data = wp_parse_args($data, array(
                    'date'=> current_time('timestamp')
                ));
        //Check date validity
        if( $data['date'] <= 0 )
            return 0;

        //Convert activity date from local timestamp to GMT mysql format
        $data['access_date'] = date_i18n( 'Y-m-d H:i:s', $data['date'], true );
        $data['access_date'] = strtotime($data['access_date']);

        //Initialise column format array
        $column_formats = fortyfourwp_data_columns();

        //White list columns
        $data = array_intersect_key($data, $column_formats);

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        $wpdb->insert($wpdb->fortyfour_logs, $data, $column_formats);

        return $wpdb->insert_id;
    }
}

/**
* Updates an activity log with supplied data
*
*@param $id int ID of the data to be updated
*@param $data array An array of column=>value pairs to be updated
*@return bool Whether the log was successfully updated.
*/
if( !function_exists( 'fortyfourwp_update_data' ) ){
    function fortyfourwp_update_data( $id, $data = array(), $params = null ){
        global $wpdb;

        //Log ID must be positive integer
        $id = absint($id);
        if( empty($id) )
             return false;

        //Initialise column format array
        $column_formats = fortyfourwp_data_columns();

        //White list columns
        $data = array_intersect_key($data, $column_formats);

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        if( !empty( $params ) && is_array( $params ) ){
            if( isset( $params['type'] ) && 'redirect_url' == $params['type'] ){
                $latest = fortyfourwp_get_data(
                    array(
                        'fields'        => array(
                                            'id',
                                        ), 
                        'where'         => 'url', 
                        'keyword'       => $params['url'], 
                        'orderby'       => 'access_date',
                        'order'         => 'desc' , 
                        'items'         => 1
                    )
                );
                $id = absint( $latest[0]->id );
            }
        }

        if ( false === $wpdb->update( $wpdb->fortyfour_logs, $data, array( 'id'=>$id ), $column_formats ) ) {
            return false;
        }

        return true;
    }
}


/**
* Retrieves datafrom the database matching $query.
* $query is an array which can contain the all keys:
*
*
*@param $query Query array
*@return array Array of matching logs. False on error.
*/
if( !function_exists( 'fortyfourwp_get_data' ) ){
    function fortyfourwp_get_data( $query=array(), $type = null ){

        global $wpdb;
        /* Parse defaults */
        $defaults = array(
            'fields'    =>  array(),
            'orderby'   =>  'id',
            'order'     =>  'desc', 
            'items'     =>  10,
            'offset'    =>  0
        );

        $query = wp_parse_args($query, $defaults);

        /* Form a cache key from the query */
        $cache_key = 'fortyfourwp_cache:'.md5( serialize($query));
        $cache = wp_cache_get( $cache_key );

        if ( false !== $cache ) {
            $cache = apply_filters('fortyfourwp_cache', $cache, $query);
            return $cache;
        }

        extract($query);

        /* SQL Select */
        //Whitelist of allowed fields
        $allowed_fields = fortyfourwp_data_columns();

        if( is_array($fields) ){
            //Convert fields to lowercase (as our column names are all lower case - see part 1)
            $fields = array_map('strtolower',$fields);

            //Sanitize by white listing
            // $fields = array_intersect($fields, $allowed_fields);
        }else{
            $fields = strtolower($fields);
        }

        //Return only selected fields. Empty is interpreted as all
        if( empty($fields) ){
            $select_sql = "SELECT * FROM {$wpdb->fortyfour_logs}";
        }elseif( 'count' == $fields ) {
            $select_sql = "SELECT COUNT(*) FROM {$wpdb->fortyfour_logs}";
        }else{
            $select_sql = "SELECT ".implode(',',$fields)." FROM {$wpdb->fortyfour_logs}";
        }

        if( 'referrers' == $type ) {
            $select_sql = "SELECT ".implode(',',$fields).", COUNT(url) as total FROM {$wpdb->fortyfour_logs}";
        }else if( 'keywords' == $type ) {
            $select_sql = "SELECT ".implode(',',$fields).", COUNT(alternative_keyword) as total FROM {$wpdb->fortyfour_logs}";
        }

        /*SQL Join */
        //We don't need this, but we'll allow it be filtered (see 'fortyfourwp_clauses' )
        $join_sql='';

        /* SQL Where */
        //Initialise WHERE
        $where_sql = 'WHERE 1=1';

        if( !empty($id) )
            $where_sql .=  $wpdb->prepare(' AND id=%d', $data_id);

        if( !empty($where) )
            $where_sql .=  $wpdb->prepare(' AND '. $where ."= '%s'", $keyword);

        if(!empty($select_type))
            $where_sql .=  $wpdb->prepare(" AND redirect_url IS NOT NULL AND 1=%d", 1);

        if( 'referrers' == $type ) {
            $where_sql .=  $wpdb->prepare(" AND referrer <>'%s'", '');
        }else if( 'keywords' == $type ) {
            $where_sql .=  $wpdb->prepare(" AND alternative_keyword IS NOT NULL AND 1=%d", 1);
        }

        /* SQL Order */
        //Whitelist order
        $order = strtoupper($order);
        $order = ( 'ASC' == $order ? 'ASC' : 'DESC' );

        $group_sql = '';
        if( isset( $groupby ) && !empty( $groupby ) ){
            switch( $groupby ){
                case 'id':
                  $group_sql = "GROUP BY (id) ";
                break;
                case 'url':
                  $group_sql = "GROUP BY (url) ";
                break;
                case 'referrer':
                   $group_sql = "GROUP BY (referrer) ";
                break;
                case 'alternative_keyword':
                   $group_sql = "GROUP BY (alternative_keyword) ";
                break;
                case 'redirect_url':
                   $group_sql = "GROUP BY (redirect_url) ";
                break;
                case 'ip':
                   $group_sql = "GROUP BY (ip) ";
                break;
                case 'access_date':
                   $group_sql = "GROUP BY (access_date) ";
                default:
                break;
            }
        }

        switch( $orderby ){
            case 'id':
                $order_sql = "ORDER BY id $order";
            break;
            case 'url':
                $order_sql = "ORDER BY url $order";
            break;
            case 'referrer':
                 $order_sql = "ORDER BY referrer $order";
            break;
            case 'alternative_keyword':
                 $order_sql = "ORDER BY alternative_keyword $order";
            break;
            case 'redirect_url':
                 $order_sql = "ORDER BY redirect_url $order";
            break;
            case 'ip':
                 $order_sql = "ORDER BY ip $order";
            break;
            case 'access_date':
                 $order_sql = "ORDER BY access_date $order";
            break;
            case 'total':
                 $order_sql = "ORDER BY total $order";
            break;
            default:
            break;
        }

        /* SQL Limit */
        $offset = absint($offset); //Positive integer
        if( $items == -1 ){
            $limit_sql  = "";
        }else{
            $items      = absint($items); //Positive integer
            $limit_sql  = "LIMIT $offset, $items";
        }

        /* Filter SQL */
        $pieces     = array( 'select_sql', 'join_sql', 'where_sql', 'group_sql', 'order_sql', 'limit_sql' );
        $clauses    = apply_filters( 'fortyfourwp_clauses', compact( $pieces ), $query );
        foreach ( $pieces as $piece )
            $$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';

        /* Form SQL statement */
        $sql = "$select_sql $where_sql $group_sql $order_sql $limit_sql";

        if( 'count' == $fields ){
            return $wpdb->get_var($sql);
        }

        /* Perform query */
        $logs = $wpdb->get_results($sql);

        /* Add to cache and filter */
        wp_cache_add( $cache_key, $logs, 24*60*60 );
        $logs = apply_filters('fortyfourwp_get_data', $logs, $query);

        return $logs;
    }
}

/**
* Deletes data from the database
*
*@param $data_id int ID of the data to be deleted
*@return bool Whether the log was successfully deleted.
*/
if( !function_exists( 'fortyfourwp_delete_data' ) ){
    function fortyfourwp_delete_data( $data_id, $type = '' ){
        global $wpdb;
        $sql = '';
        switch ( $type ) {
            case 'url':
                //Delete Logs per Url
                $data_id = absint($data_id);
                if( empty($data_id) )
                     return false;

                $data = fortyfourwp_get_data(
                            array(
                                'fields'        => array(
                                                    'url',
                                                ), 
                                'where'         => 'id', 
                                'keyword'       => $data_id, 
                                'items'         => 1,
                            )
                        );
                if(  isset( $data[0]->url ) && !empty( $data[0]->url ) ){
                    $sql = $wpdb->prepare("DELETE from {$wpdb->fortyfour_logs} WHERE url = '%s'", $data[0]->url);
                }
                break;
            
            default:
                //Log ID must be positive integer
                $data_id = absint($data_id);
                if( empty($data_id) )
                     return false;

                do_action('fortyfourwp_delete_data',$data_id);
                $sql = $wpdb->prepare("DELETE from {$wpdb->fortyfour_logs} WHERE id = %d", $data_id);
                break;
        }

        if( !$wpdb->query( $sql ) )
             return false;

        // do_action('fortyfourwp_deleted_log',$data_id);

        return true;
    }
}

if( !function_exists( 'fortyfourwp_search_sql' ) ){
    function fortyfourwp_search_sql( $string, $cols ) {
        global $wpdb;

        if ( method_exists( $wpdb, 'esc_like' ) ) {
            $like = '%' . $wpdb->esc_like( $string ) . '%';
        }else{
            $like = '%' . $wpdb->like_escape( $string ) . '%';
        }
        

        $searches = array();
        foreach ( $cols as $col ) {
            $searches[] = $wpdb->prepare( "$col LIKE %s", $like );
        }

        return ' AND (' . implode(' OR ', $searches) . ')';
    }
}
?>
