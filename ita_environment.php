<?php
defined('ABSPATH') or die("No script kiddies please!");
class ENVIRONMENT_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'environment';
		$this->name_single	= 'Ambiente';
		$this->name_plural	= 'Ambientes';
		$this->icon			= '<i class="fa fa-truck"></i>';
		$this->parent_slug	= $ita_vars['admin_menu_slug'];							//main, admin, super
		$this->menu_slug	= $ita_vars[$this->class_name.'_menu_slug'];			//Class Menu Slug
		$this->plugin_post	= $ita_vars['plugin_post'];								//used for validations
		$this->capability	= $ita_vars[$this->class_name.'_menu_cap'];				//Class capabilities
		$this->tbl_name		= $wpdb->prefix.$ita_vars[$this->class_name.'_tbl_name'];
		$this->db_version	= '2';
		$charset_collate = $wpdb->get_charset_collate();
		$this->crt_tbl_sql	=														//SQL Sentence for create Clas table
								"CREATE TABLE ".$this->tbl_name." (
								id tinyint unsigned not null auto_increment,
								name varchar(60) not null,
								shortname varchar(15) not null,
								env_order tinyint unsigned not null,
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
			//field_name			field_type				,required			form_size		maxchar				desc
			'id'		=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'name'		=> array('field_type'=>'text'		,'required'=>true,	'size'=>'M',	'maxchar'=>60,		'field_desc'=>'Nombre Completo'),
			'shortname'	=> array('field_type'=>'text'		,'required'=>true,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'Nombre Corto'),
			'env_order'	=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>2,		'field_desc'=>'Orden Visual'),
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
		$DB=$this->db_fields;
		$rows = self::get_all();
		$contents=array();
		foreach($rows as $row):
			array_push($contents,
				array(
					$row['id'],
					$row['shortname'],
					$row['name'],
					$row['env_order'],
					)
				);
		endforeach;
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array('id',$DB['shortname']['field_desc'],$DB['name']['field_desc'],$DB['env_order']['field_desc']);
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
		$DB=$this->db_fields;
		$fields=array(
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
			array(
				'field_name'	=>	'env_order',
				'field_desc'	=>	$DB['env_order']['field_desc'],
				'field_type'	=>	$DB['env_order']['field_type'],
				'field_value'	=>	'',
				'required'		=>	$DB['env_order']['required'],
				'size'			=>	$DB['env_order']['size'],
				'maxchar'		=>	$DB['env_order']['maxchar']
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
		$item=self::get_single($id);
		$DB=$this->db_fields;
		$fields=array(
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
				'field_flag'	=>	'',				//en caso de dropdown
				'required'		=>	$DB['name']['required'],
				'size'			=>	$DB['name']['size'],
				'maxchar'		=>	$DB['name']['maxchar']
			),
			array(
				'field_name'	=>	'env_order',
				'field_desc'	=>	$DB['env_order']['field_desc'],
				'field_type'	=>	$DB['env_order']['field_type'],
				'field_value'	=>	$item['env_order'],
				'field_flag'	=>	'',				//en caso de dropdown
				'required'		=>	$DB['env_order']['required'],
				'size'			=>	$DB['env_order']['size'],
				'maxchar'		=>	$DB['env_order']['maxchar']
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

global $ENVIRONMENT;
$ENVIRONMENT =new ENVIRONMENT_CLASS();
?>