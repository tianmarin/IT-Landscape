<?php
defined('ABSPATH') or die("No script kiddies please!");
class LANDSCAPE_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'landscape';
		$this->name_single	= 'Landscape';
		$this->name_plural	= 'Landscapes';
		$this->icon			= '<i class="fa fa-sitemap"></i>';
		$this->parent_slug	= $ita_vars['main_menu_slug'];							//main, admin, super
		$this->menu_slug	= $ita_vars[$this->class_name.'_menu_slug'];			//Class Menu Slug
		$this->plugin_post	= $ita_vars['plugin_post'];								//used for validations
		$this->capability	= $ita_vars[$this->class_name.'_menu_cap'];				//Class capabilities
		$this->tbl_name		= $wpdb->prefix.$ita_vars[$this->class_name.'_tbl_name'];
		$this->db_version	= '1';
		$charset_collate	= $wpdb->get_charset_collate();
		$this->crt_tbl_sql	=														//SQL Sentence for create Clas table
								"CREATE TABLE ".$this->tbl_name." (
								id smallint unsigned not null auto_increment,
								customer_id tinyint unsigned not null,
								name varchar(60) not null,
								shortname varchar(15) not null,
								UNIQUE KEY id (id)
								) $charset_collate;";
							
		$this->db_fields	= array(
			/*
			field_name		:	Nombre del campo a nivel de DB
			field_type		:	Tipo de Dato para validacion
			required		:	Flag de obligatoriedad del dato (NOT NULL)
								id
								nat_number
								text
			size			:	Tamaño del campo para formularios (valido solo para inputs tipo texto)
								XS		15%
								S		30%
								M		50%
								L		75%
								XL		100%
			maxchar			:	Máximo número de caracters	(null es indefinido)
			*/
			//field_name			field_type					required			form_size		maxchar				desc
			'id'			=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'customer_id'	=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Cliente'),
			'name'			=> array('field_type'=>'text'		,'required'=>true,	'size'=>'M',	'maxchar'=>60,		'field_desc'=>'Nombre Completo'),
			'shortname'		=> array('field_type'=>'text'		,'required'=>true,	'size'=>'XS',	'maxchar'=>15,		'field_desc'=>'Nombre Corto'),
		);
		register_activation_hook(plugin_dir_path(__FILE__)."index.php", array( $this, 'db_install') );
		add_action('admin_menu', array( &$this , "ita_register_class_submenu_page" ) );
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------------
	/**
	* Creates the main content of the administation page
	*
	* @global wpdb $wpdb
	* @since 1.0
	* @author Cristian Marin
	* @package WordPress
	*/
	function class_menu_main(){
		//Global
		global $wpdb;
		global $ita_vars;
		global $CUSTOMER;
		$DB=$this->db_fields;
		$sql="SELECT
				aa.id as id,
				aa.name as name,
				aa.shortname as shortname,
				bb.id as customer_id,
				bb.name as customer_name,
				bb.shortname as customer_shortname
			FROM
				(($this->tbl_name as aa)
				LEFT JOIN $CUSTOMER->tbl_name as bb ON aa.customer_id=bb.id)";
		$rows = $this->get_sql($sql);
		$contents=array();
		foreach($rows as $row):
			array_push($contents,
				array(
					$row['id'],
					$row['customer_shortname'].'<br/><small>'.$row['customer_name'].'</small>',
					$row['shortname'],
					$row['name'],
					'<code>[ita landscape='.$row['id'].']</code>'
					)
				);
		endforeach;
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array('id','Cliente',$DB['shortname']['field_desc'],$DB['name']['field_desc'],"C&oacute;digo");
		ita_class_menu_main(
			$title,
			$this->menu_slug,
			$add_new,
			$this->plugin_post,
			$titles,
			$contents
		);
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------------
	/**
	* Form to add a single Class registry to the system
	*
	* @since 1.0
	* @author Cristian Marin
	*/
	public function new_form(){
		global $CUSTOMER;
		$DB=$this->db_fields;
//		$customers=$CUSTOMER->get_all();
		$customer_list=array();
		foreach($CUSTOMER->get_all() as $customer):
			array_push($customer_list, array($customer['id'],$customer['name']));
		endforeach;
		$fields=array(
			array(
				'field_name'	=>	'customer_id',
				'field_desc'	=>	$DB['customer_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$customer_list,
				'selected'		=>	(sizeof($customer_list)>1)?null:'1',
				'required'		=>	$DB['customer_id']['required'],
			),
			array(
				'field_name'	=>	'shortname',
				'field_desc'	=>	$DB['shortname']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['shortname']['required'],
				'size'			=>	$DB['shortname']['size'],
				'maxchar'		=>	$DB['shortname']['maxchar']
			),
			array(
				'field_name'	=>	'name',
				'field_desc'	=>	$DB['name']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['name']['required'],
				'size'			=>	$DB['name']['size'],
				'maxchar'		=>	$DB['name']['maxchar']
			),
		);
		$title = "Agregar nuevo ".$this->name_single;
		ita_register_script_formvalidator($this->menu_slug);
		ita_class_form(
			"add",
			$title,
			$this->menu_slug,
			$this->plugin_post,
			$fields
		);		

	}
//---------------------------------------------------------------------------------------------------------------------------------------------------------
	/**
	* Form to edit a single Class registry to the system
	*
	* @since 1.0
	* @author Cristian Marin
	*/
	function edit_form($id=0){
		global $CUSTOMER;
		$customer_list=array();
		foreach($CUSTOMER->get_all() as $customer):
			array_push($customer_list, array($customer['id'],$customer['name']));
		endforeach;
		$item=self::get_single($id);
		$DB=$this->db_fields;
		$fields=array(
			array(
				'field_name'	=>	'customer_id',
				'field_desc'	=>	$DB['customer_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$customer_list,
				'selected'		=>	$item['customer_id'],				//en caso de dropdown
				'required'		=>	$DB['customer_id']['required'],
				'size'			=>	$DB['customer_id']['size'],
				'maxchar'		=>	$DB['customer_id']['maxchar']
			),
			array(
				'field_name'	=>	'shortname',
				'field_desc'	=>	$DB['shortname']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['shortname'],
				'selected'	=>	'',				//en caso de dropdown
				'required'		=>	$DB['shortname']['required'],
				'size'			=>	$DB['shortname']['size'],
				'maxchar'		=>	$DB['shortname']['maxchar']
			),
			array(
				'field_name'	=>	'name',
				'field_desc'	=>	$DB['name']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['name'],
				'selected'	=>	'',				//en caso de dropdown
				'required'		=>	$DB['name']['required'],
				'size'			=>	$DB['name']['size'],
				'maxchar'		=>	$DB['name']['maxchar']
			),
		);
		$title="Editar ".$this->name_single;
		ita_register_script_formvalidator($this->menu_slug);
		ita_class_form(
			"update",
			$title,
			$this->menu_slug,
			$this->plugin_post,
			$fields,
			$id
		);
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------------

//END OF CLASS	
}

global $LANDSCAPE;
$LANDSCAPE =new LANDSCAPE_CLASS();
?>