<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Dotmailer Contact Form 7 Plugin N2DMCF7_Form_Handler
 *
 * 
 *
 * @package     dotmailer_cf7
 * @version     1.0.0
 * @category    Class
 * @author      n2 Digital Media
 */

class N2DMCF7_Form_Handler {

    private $dm_addressbook;
    private $dm_settings;
    private $dm_username;
    private $dm_password;
    private $dm_client;
    private $field_type_name_array = array();

    public function __construct() {

        add_action( 'save_post', array($this, 'filter_cf7_fields_on_save') );

        add_action('wpcf7_before_send_mail', array( $this, 'check_wpcf7_email_and_process'));
    }

    public function initialise_settings(){

        $this->dm_settings = n2dmcf7_get_options(); // connect to database options
        $this->dm_username = $this->dm_settings['dotmailer_username'];
        $this->dm_password = $this->dm_settings['dotmailer_password'];

        $params = array('username' => $this->dm_username, 'password' => $this->dm_password);
        
        $this->dm_client = new DotMailerConnect($this->dm_username, $this->dm_password);

        
    }

    public function filter_cf7_fields_on_save(){

        $this->initialise_settings();
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        $this->check_posted_form_for_dm_fields();
        
        $this->compare_field_arrays();


    }

    public function check_posted_form_for_dm_fields(){
        
        foreach ($_POST as $key => $value) {
            if($key == 'wpcf7-form'){
                $form = $value;

                // get value of "dm_addressbook"
                if(strpos($form, "dm_addressbook")){
                    $ab_value = explode('\"', $form); // ab short for addressbook
                    $this->dm_addressbook = $ab_value[1];
                }

                $needle = "[";
                $last_pos = 0;
                $positions = array();

                // check through the rest of the post for [fields] and get their start position index values.

                while (($last_pos = strpos($form, $needle, $last_pos)) !== false) {
                    $positions[] = $last_pos;
                    $last_pos = $last_pos + 1;
                }
                // find all instances of [ and take a substring of 40 chars
                foreach ($positions as $start) {
                    $string = substr($form, $start, 40);
                    if(strpos($string, "dm_") !== false){
                        $words = explode(" ", $string);
                        // if the substring contacts "dm_" the word directly after the [ is the type
                        // the second word is the field name
                        $type = substr($words[0], 1);
                        if(strpos($words[1], "]") !== false){
                            $word = explode("]", $words[1]);
                            $name = $word[0];
                        }
                        else{
                            $name = $words[1];  
                        }
                        // Add both the type and name to an array
                        if($type != "hidden" && $type != 'submit'){
                            $this->field_type_name_array[][$type] = $name;
                        }
                    }
                }

                break;
            }
        }
    }


    public function compare_field_arrays(){

        $new_field_keys = array();

        $existig_fields = $this->dm_client->listDataFields();
        $existing_field_names = array();

        for ($i=0; $i < count($existig_fields) ; $i++) { 
            $existing_field_names[] = $existig_fields[$i]->Name;
        }

        if(count($this->field_type_name_array) > 0){ 
            foreach ($this->field_type_name_array as $field_type_name) {

                foreach ($field_type_name as $key => $value){
                    // sanitise the value to be all caps and remove the dm_

                    $value = explode("dm_", $value);
                    $value = strtoupper($value[1]);
                    $value = str_replace("-", "_", $value);

                    $key = $this->sanitise_field_types($key, $value);
                    
                    // if the value is NOT inside the existing fields array, create a new field on dotmailer
                    if(!in_array($value, $existing_field_names)){
                        
                        $this->dm_client->AddDataField($value, $key);
                    }
                }
            }
        }
    }

    public function sanitise_field_types($type, $value){
        switch ($type){
            case "text":
                $datatype = "String";
                break;
            case "date":
                $datatype = "Date";
                $value = date("Y-m-d", strtotime($value));
                break;
            case "number":
                $datatype = "Numeric";
                break;
            case "range":
                $datatype = "Numeric";
                break;
            case "acceptance":
                $datatype = "Boolean";
                if ($value = "FALSE") {
                    $value = 0;
                } else {
                    $value = 1;
                }
                break;
            default:
                $datatype = 'String';
                break;
        }
        $type = $datatype;
        return $type;

    }

    public function get_xsd_value($type){
        $xsd;
        switch ($type){
            case "string":
                $xsd = XSD_STRING;
                break;
            case "date":
                $xsd = XSD_DATE;
                break;
            case "int":
                $xsd = XSD_INT;
                break;
            case "boolean":
                $xsd = XSD_BOOLEAN;
                break;
            default:
                $xsd = XSD_STRING;
                break;
        }
        return $xsd;
    }


    public function check_wpcf7_email_and_process(){
        
        $this->initialise_settings();

        $addressbook;
        $email;

        $keys = array(); // array of all keys
        $values = array(); // array of all values
        
        $field_type; // we'll use this to get the field type from the existing fields
        $xsd_value; // we'll use the get_xsd_value function to return the xsd value for the field type
        $fields_array = array();


        // check for the address book and email address (mandatory fields)
        foreach ($_POST as $k => $v) {
            if(strpos($k, "dm_") !== false){
                if(strpos($k, 'addressbook') !== false){
                    $addressbook = $v;
                }
                elseif(strpos($k, 'emailaddress') !== false){
                    $email = $v;
                }
                else{
                   $fields_array[][$k] = $v; 
                }
            }
        }
        _log($fields_array);
        $datafields = array();

        // get existing fields to match new fields to
        $existig_fields = $this->dm_client->listDataFields();

        //Loop through all other dm_ fields
        foreach ($fields_array as $dm_field) {
            foreach ($dm_field as $key => $value) {
                $key = explode("dm_", $key);
                $key = strtoupper(rtrim($key[1], ']'));
                $key = str_replace("-", "_", $key);

                foreach ($existig_fields as $data_field) {
                    if($key == $data_field->Name){

                        $field_type = strtolower($data_field->Type);
                        if($field_type == 'numeric'){
                            $field_type = 'int';
                        }

                        $xsd_value = $this->get_xsd_value($field_type);
                        
                        $value = new SoapVar($value, $xsd_value, $field_type, "http://www.w3.org/2001/XMLSchema");
                    
                        $keys[] = $key;
                        $values[] = $value;
                        continue;
                    }
                }
                
            }
        }

        $datafields = array('Keys' => $keys, 'Values' => $values);

        $this->dm_client->AddContactToAddressBook($email, $addressbook, $datafields);
        
    }
}

return new N2DMCF7_Form_Handler();