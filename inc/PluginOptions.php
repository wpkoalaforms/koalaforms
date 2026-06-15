<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class PluginOptions {

    private static function optionKey(){
        return AppUtility::pluginKey('_gsettings');
    }

    public static function save_options($options){
        update_option(self::optionKey(),$options);
    }
    
    public static function get_options(){
        $options= get_option(self::optionKey());
        $default_options= self::get_default_options();
        return wp_parse_args($options,$default_options);
    }
    
    public static function get_default_options(){
        $options= array();
        $options= apply_filters(AppUtility::pluginKey('_default_global_options'),$options); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        return $options;
    }
    
    public static function create_default_options(){
        $options= self::get_default_options();
        $global_settings= self::get_options();
        if(empty($global_settings)){
            $global_settings= $options;
        }
        else{
            foreach($options as $key=>$default){
                if(!isset($global_settings[$key])){
                    $global_settings[$key]= $default;
                }
            }
        }
        self::save_options($global_settings);
    }
}