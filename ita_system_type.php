<?php
defined('ABSPATH') or die("No script kiddies please!");
class SYSTEM_TYPE_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'system_type';
		$this->name_single	= 'Tipo de Sistema';
		$this->name_plural	= 'Tipos de Sistema';
		$this->icon			= '<i class="fa fa-cubes"></i>';
		$this->parent_slug	= $ita_vars['admin_menu_slug'];							//main, admin, super
		$this->menu_slug	= $ita_vars[$this->class_name.'_menu_slug'];			//Class Menu Slug
		$this->plugin_post	= $ita_vars['plugin_post'];								//used for validations
		$this->capability	= $ita_vars[$this->class_name.'_menu_cap'];				//Class capabilities
		$this->tbl_name		= $wpdb->prefix.$ita_vars[$this->class_name.'_tbl_name'];
		$this->db_version	= '1';
		$charset_collate = $wpdb->get_charset_collate();
		$this->crt_tbl_sql	=														//SQL Sentence for create Clas table
								"CREATE TABLE ".$this->tbl_name." (
									id smallint unsigned not null auto_increment,
									vendor_id tinyint unsigned not null,
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
			size			:	Tama–o del campo para formularios (valido solo para inputs tipo texto)
								XS		15%
								S		30%
								M		50%
								L		75%
								XL		100%
			maxchar			:	M‡ximo nœmero de caracters	(null es indefinido)
			*/
			//field_name			field_type					required			form_size		maxchar				desc
			'id'			=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'vendor_id'		=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Proveedor'),
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
		global $VENDOR;
		$DB=$this->db_fields;
		//------------------------------------------------------------------------------------------------
		$sql="SELECT
				aa.id as id,
				aa.name as name,
				aa.shortname as shortname,
				bb.id as vendor_id,
				bb.name as vendor_name,
				bb.shortname as vendor_shortname
			FROM
				(($this->tbl_name as aa)
				LEFT JOIN ".$VENDOR->tbl_name." as bb ON aa.vendor_id=bb.id)";
		if(isset($_GET['vendor_id'])):
			$sql.=" WHERE bb.id = ".$_GET['vendor_id'];
		endif;
		$rows = $this->get_sql($sql);
		$contents=array();
		foreach($rows as $row):
			array_push($contents,
				array(
					$row['id'],
					$row['vendor_shortname'].'<br/><small>'.$row['vendor_name'].'</small>',
					$row['shortname'],
					$row['name'],
					)
				);
		endforeach;
		//------------------------------------------------------------------------------------------------
		$vendor_filter=array();
		foreach($VENDOR->get_all() as $vendor){
			array_push($vendor_filter,array($vendor['id'], $vendor['shortname']." (".$vendor['name'].")"));
		}
		$filters=array(
			//array(filter_name		,filter_desc						,filter_type	,filter_values		,auto_change)
			array('vendor_id'		,$DB['vendor_id']['field_desc']		,'dropdown'		,$vendor_filter		,''	),
			array('shortname'		,$DB['shortname']['field_desc']		,'text'			,''					,''	),
			array('name'			,$DB['name']['field_desc']			,'text'			,''					,''	),
		);
		//------------------------------------------------------------------------------------------------
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array('id',$DB['vendor_id']['field_desc'],$DB['shortname']['field_desc'],$DB['name']['field_desc']);
		//------------------------------------------------------------------------------------------------
		ita_class_menu_main(
			$title,
			$this->menu_slug,
			$add_new,
			$this->plugin_post,
			$titles,
			$contents,
			$filters
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
		global $VENDOR;
		$DB=$this->db_fields;
		$vendor_list=array();
		foreach($VENDOR->get_all() as $vendor):
			array_push($vendor_list, array($vendor['id'],$vendor['name']));
		endforeach;
		$fields=array(
			array(
				'field_name'	=>	'vendor_id',
				'field_desc'	=>	$DB['vendor_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$vendor_list,
				'required'		=>	$DB['vendor_id']['required'],
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
		global $VENDOR;
		$vendor_list=array();
		foreach($VENDOR->get_all() as $vendor):
			array_push($vendor_list, array($vendor['id'],$vendor['name']));
		endforeach;
		$item=self::get_single($id);
		$DB=$this->db_fields;
		$fields=array(
			array(
				'field_name'	=>	'vendor_id',
				'field_desc'	=>	$DB['vendor_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$vendor_list,
				'selected'	=>	$item['vendor_id'],				//en caso de dropdown
				'required'		=>	$DB['vendor_id']['required'],
				'size'			=>	$DB['vendor_id']['size'],
				'maxchar'		=>	$DB['vendor_id']['maxchar']
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

global $SYSTEM_TYPE;
$SYSTEM_TYPE =new SYSTEM_TYPE_CLASS();
?>