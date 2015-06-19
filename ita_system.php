<?php
defined('ABSPATH') or die("No script kiddies please!");
class SYSTEM_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'system';
		$this->name_single	= 'Sistema';
		$this->name_plural	= 'Sistemas';
		$this->icon			= '<i class="fa fa-cube"></i>';
		$this->parent_slug	= $ita_vars['main_menu_slug'];							//main, admin, super
		$this->menu_slug	= $ita_vars[$this->class_name.'_menu_slug'];			//Class Menu Slug
		$this->plugin_post	= $ita_vars['plugin_post'];								//used for validations
		$this->capability	= $ita_vars[$this->class_name.'_menu_cap'];				//Class capabilities
		$this->tbl_name		= $wpdb->prefix.$ita_vars[$this->class_name.'_tbl_name'];
		$this->db_version	= '1';
		$charset_collate = $wpdb->get_charset_collate();
		$this->crt_tbl_sql	=														//SQL Sentence for create Clas table
								"CREATE TABLE ".$this->tbl_name." (
									id smallint unsigned not null auto_increment,
									landscape_id smallint unsigned not null,
									system_type_id tinyint unsigned null,
									environment_id tinyint unsigned null,
									product_id smallint unsigned null,
									sid varchar(15) not null,
									inst_no varchar(15) not null,
									syst_no varchar(15) not null,
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
			'id'				=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'landscape_id'		=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Landscape'),
			'system_type_id'	=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Tipo de Sistema'),
			'environment_id'	=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Ambiente'),
			'product_id'		=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Producto'),
			'sid'				=> array('field_type'=>'text'		,'required'=>true,	'size'=>'XS',	'maxchar'=>15,		'field_desc'=>'SID'),
			'inst_no'			=> array('field_type'=>'text'		,'required'=>false,	'size'=>'M',	'maxchar'=>15,		'field_desc'=>'No de Instalaci&oacute;n'),
			'syst_no'			=> array('field_type'=>'text'		,'required'=>false,	'size'=>'M',	'maxchar'=>15,		'field_desc'=>'No de Sistema'),
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
		global $LANDSCAPE;
		global $SYSTEM_TYPE;
		global $ENVIRONMENT;
		global $PRODUCT;
		global $SO;
		$DB=$this->db_fields;
		//------------------------------------------------------------------------------------------------
		$sql="SELECT
				aa.id as id,
				aa.sid as sid,
				bb.id as landscape_id,
				bb.name as landscape_name,
				bb.shortname as landscape_shortname,
				cc.id as system_type_id,
				cc.name as system_type_name,
				cc.shortname as system_type_shortname,
				dd.id as environment_id,
				dd.name as environment_name,
				dd.shortname as environment_shortname,
				ee.id as product_id,
				ee.name as product_name,
				ee.shortname as product_shortname
			FROM
				((((($this->tbl_name as aa)
				LEFT JOIN $LANDSCAPE->tbl_name as bb ON aa.landscape_id=bb.id)
				LEFT JOIN $SYSTEM_TYPE->tbl_name as cc ON aa.system_type_id=cc.id)
				LEFT JOIN $ENVIRONMENT->tbl_name as dd ON aa.environment_id=dd.id)
				LEFT JOIN $PRODUCT->tbl_name as ee ON aa.product_id=ee.id)
				";
		$SQL_WHERE=array();
		if( isset($_GET['landscape_id']) && $_GET['landscape_id']!=0):
			array_push($SQL_WHERE,"bb.id=".$_GET['landscape_id']);
		endif;
		if( isset($_GET['system_type_id']) && $_GET['system_type_id']!=0):
			array_push($SQL_WHERE,"cc.id=".$_GET['system_type_id']);
		endif;
		if( isset($_GET['environment_id']) && $_GET['environment_id']!=0):
			array_push($SQL_WHERE,"dd.id=".$_GET['environment_id']);
		endif;
		if( isset($_GET['product_id']) && $_GET['product_id']!=0):
			array_push($SQL_WHERE,"ee.id=".$_GET['product_id']);
		endif;
		if(sizeof($SQL_WHERE)>0){
			$sql.=" WHERE ".implode(" AND ",$SQL_WHERE);
		}
				
		$rows = $this->get_sql($sql);
		$contents=array();
		foreach($rows as $row):
			array_push($contents,
				array(
					$row['id'],
					$row['landscape_shortname'].'<br/><small>'.$row['landscape_name'].'</small>',
					$row['system_type_shortname'].'<br/><small>'.$row['system_type_name'].'</small>',
					$row['environment_shortname'].'<br/><small>'.$row['environment_name'].'</small>',
					$row['product_name'].'<br/><small>'.$row['product_name'].'</small>',
					$row['sid'],
					"<code>[ita system=".$row['id']." deep=1]</code>",
					)
				);
		endforeach;
		//------------------------------------------------------------------------------------------------
		$landscape_filter=array();
		foreach($LANDSCAPE->get_all() as $landscape){
			array_push($landscape_filter,array($landscape['id'], $landscape['shortname']." (".$landscape['name'].")"));
		}
		$system_type_filter=array();
		foreach($SYSTEM_TYPE->get_all() as $system_type){
			array_push($system_type_filter,array($system_type['id'], $system_type['shortname']." (".$system_type['name'].")"));
		}
		$environment_filter=array();
		foreach($ENVIRONMENT->get_all() as $environment){
			array_push($environment_filter,array($environment['id'], $environment['shortname']." (".$environment['name'].")"));
		}
		$product_filter=array();
		foreach($PRODUCT->get_all() as $product){
			array_push($product_filter,array($product['id'], $product['shortname']." (".$product['name'].")"));
		}
		$filters=array(
			//array(filter_name			,filter_desc							,filter_type	,filter_values					,auto_change)
			array('landscape_id'		,$DB['landscape_id']['field_desc']		,'dropdown'		,$landscape_filter				,''	),
			array('system_type_id'		,$DB['system_type_id']['field_desc']	,'dropdown'		,$system_type_filter			,''	),
			array('environment_id'		,$DB['environment_id']['field_desc']	,'dropdown'		,$environment_filter			,''	),
			array('product_id'			,$DB['product_id']['field_desc']		,'dropdown'		,$product_filter				,''	),
			array('sid'					,$DB['sid']['field_desc']				,'text'			,''								,''	),
			array('code'				,"C&oacute;digo"						,'text'			,''								,''	),
		);
		//------------------------------------------------------------------------------------------------
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array(
						'id',
						$DB['landscape_id']['field_desc'],
						$DB['system_type_id']['field_desc'],
						$DB['product_id']['field_desc'],
						$DB['environment_id']['field_desc'],
						$DB['sid']['field_desc'],
						"C&oacute;digo",
					);
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
		global $LANDSCAPE;
		global $SYSTEM_TYPE;
		global $ENVIRONMENT;
		global $PRODUCT;
		$DB=$this->db_fields;

		$landscape_list=array();
		foreach($LANDSCAPE->get_all() as $landscape):
			array_push($landscape_list, array($landscape['id'],$landscape['name']));
		endforeach;

		$system_type_list=array();
		foreach($SYSTEM_TYPE->get_all() as $system_type):
			array_push($system_type_list, array($system_type['id'],$system_type['name']));
		endforeach;

		$environment_list=array();
		foreach($ENVIRONMENT->get_all() as $environment):
			array_push($environment_list, array($environment['id'],$environment['name']));
		endforeach;

		$product_list=array();
		foreach($PRODUCT->get_all() as $product):
			array_push($product_list, array($product['id'],$product['name']));
		endforeach;

		$fields=array(
			array(
				'field_name'	=>	'landscape_id',
				'field_desc'	=>	$DB['landscape_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$landscape_list,
				'required'		=>	$DB['landscape_id']['required'],
			),
			array(
				'field_name'	=>	'system_type_id',
				'field_desc'	=>	$DB['system_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$system_type_list,
				'required'		=>	$DB['system_type_id']['required'],
			),
			array(
				'field_name'	=>	'environment_id',
				'field_desc'	=>	$DB['environment_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$environment_list,
				'required'		=>	$DB['environment_id']['required'],
			),
			array(
				'field_name'	=>	'product_id',
				'field_desc'	=>	$DB['product_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$product_list,
				'required'		=>	$DB['product_id']['required'],
			),
			
			array(
				'field_name'	=>	'sid',
				'field_desc'	=>	$DB['sid']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['sid']['required'],
				'size'			=>	$DB['sid']['size'],
				'maxchar'		=>	$DB['sid']['maxchar']
			),
			array(
				'field_name'	=>	'inst_no',
				'field_desc'	=>	$DB['inst_no']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['inst_no']['required'],
				'size'			=>	$DB['inst_no']['size'],
				'maxchar'		=>	$DB['inst_no']['maxchar']
			),
			array(
				'field_name'	=>	'syst_no',
				'field_desc'	=>	$DB['syst_no']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['syst_no']['required'],
				'size'			=>	$DB['syst_no']['size'],
				'maxchar'		=>	$DB['syst_no']['maxchar']
			),
		);

		ita_register_script_formvalidator($this->menu_slug);
		$title = "Agregar nuevo ".$this->name_single;
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
		global $LANDSCAPE;
		global $SYSTEM_TYPE;
		global $ENVIRONMENT;
		global $PRODUCT;
		$DB=$this->db_fields;

		$landscape_list=array();
		foreach($LANDSCAPE->get_all() as $landscape):
			array_push($landscape_list, array($landscape['id'],$landscape['name']));
		endforeach;

		$system_type_list=array();
		foreach($SYSTEM_TYPE->get_all() as $system_type):
			array_push($system_type_list, array($system_type['id'],$system_type['name']));
		endforeach;

		$environment_list=array();
		foreach($ENVIRONMENT->get_all() as $environment):
			array_push($environment_list, array($environment['id'],$environment['name']));
		endforeach;

		$product_list=array();
		foreach($PRODUCT->get_all() as $product):
			array_push($product_list, array($product['id'],$product['name']));
		endforeach;

		$item=self::get_single($id);
		$fields=array(
			array(
				'field_name'	=>	'landscape_id',
				'field_desc'	=>	$DB['landscape_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['landscape_id'],				//en caso de dropdown
				'field_value'	=>	$landscape_list,
				'required'		=>	$DB['landscape_id']['required'],
			),
			array(
				'field_name'	=>	'system_type_id',
				'field_desc'	=>	$DB['system_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['system_type_id'],				//en caso de dropdown
				'field_value'	=>	$system_type_list,
				'required'		=>	$DB['system_type_id']['required'],
			),
			array(
				'field_name'	=>	'environment_id',
				'field_desc'	=>	$DB['environment_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['environment_id'],				//en caso de dropdown
				'field_value'	=>	$environment_list,
				'required'		=>	$DB['environment_id']['required'],
			),
			array(
				'field_name'	=>	'product_id',
				'field_desc'	=>	$DB['product_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['product_id'],				//en caso de dropdown
				'field_value'	=>	$product_list,
				'required'		=>	$DB['product_id']['required'],
			),
			
			array(
				'field_name'	=>	'sid',
				'field_desc'	=>	$DB['sid']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	strtoupper($item['sid']),
				'required'		=>	$DB['sid']['required'],
				'size'			=>	$DB['sid']['size'],
				'maxchar'		=>	$DB['sid']['maxchar']
			),
			array(
				'field_name'	=>	'inst_no',
				'field_desc'	=>	$DB['inst_no']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['inst_no'],
				'required'		=>	$DB['inst_no']['required'],
				'size'			=>	$DB['inst_no']['size'],
				'maxchar'		=>	$DB['inst_no']['maxchar']
			),
			array(
				'field_name'	=>	'syst_no',
				'field_desc'	=>	$DB['syst_no']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['syst_no'],
				'required'		=>	$DB['syst_no']['required'],
				'size'			=>	$DB['syst_no']['size'],
				'maxchar'		=>	$DB['syst_no']['maxchar']
			),
		);
	
	
//------------	
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

global $SYSTEM;
$SYSTEM =new SYSTEM_CLASS();
?>