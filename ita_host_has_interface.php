<?php
defined('ABSPATH') or die("No script kiddies please!");
class IP_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'host_has_interface';
		$this->name_single	= 'IP';
		$this->name_plural	= 'IPs';
		$this->icon			= '<i class="fa fa-wifi"></i>';
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
									host_id smallint unsigned not null,
									interface_id smallint unsigned not null,
									ip varchar(15) not null,
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
			size			:	Tama–o del campo para formularios (valido interfacelo para inputs tipo texto)
								XS		15%
								S		30%
								M		50%
								L		75%
								XL		100%
			maxchar			:	M‡ximo nœmero de caracters	(null es indefinido)
			*/
			//field_name			field_type					required			form_size		maxchar				desc
			'id'				=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'host_id'			=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Host'),
			'interface_id'		=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Interfaz'),
			'ip'				=> array('field_type'=>'text'		,'required'=>false,	'size'=>'S',	'maxchar'=>15,		'field_desc'=>'IP'),
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
		global $HOST;
		global $INTERFACE;
		$DB=$this->db_fields;
		//------------------------------------------------------------------------------------------------
		$sql="SELECT
				aa.id as id,
				aa.ip as ip,
				bb.id as host_id,
				bb.name as host_name,
				bb.hostname as host_hostname,
				cc.id as interface_id,
				cc.name as interface_name,
				cc.shortname as interface_shortname,
				cc.ip as interface_ip
			FROM
				((($this->tbl_name as aa)
				LEFT JOIN $HOST->tbl_name as bb ON aa.host_id=bb.id)
				LEFT JOIN $INTERFACE->tbl_name as cc ON aa.interface_id=cc.id)
				";
		//funcion recursiva con par‡metros get
		$SQL_WHERE=array();
		if( isset($_GET['host_id']) && $_GET['host_id']!=0):
			array_push($SQL_WHERE,"bb.id=".$_GET['host_id']);
		endif;
		if( isset($_GET['interface_id']) && $_GET['interface_id']!=0):
			array_push($SQL_WHERE,"cc.id=".$_GET['interface_id']);
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
					$row['host_hostname'].'<br/><small>'.$row['host_name'].'</small>',
					$row['interface_name'].'<br/><small>'.$row['interface_shortname'].'</small>',
					$row['ip'],
					)
				);
		endforeach;
		//------------------------------------------------------------------------------------------------
		$host_filter=array();
		foreach($HOST->get_all() as $host){
			array_push($host_filter,array($host['id'], $host['hostname']." (".$host['name'].")"));
		}
		$interface_filter=array();
		foreach($INTERFACE->get_all() as $interface){
			array_push($interface_filter,array($interface['id'], $interface['shortname']." (".$interface['name'].")"));
		}
		$filters=array(
			//array(filter_name			,filter_desc							,filter_type	,filter_values					,auto_change)
			array('host_id'				,$DB['host_id']['field_desc']			,'dropdown'		,$host_filter					,''	),
			array('interface_id'		,$DB['interface_id']['field_desc']		,'dropdown'		,$interface_filter				,''	),
			array('ip'					,$DB['ip']['field_desc']				,'text'			,''								,''	),
		);
		//------------------------------------------------------------------------------------------------
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array(
						'id',
						$DB['host_id']['field_desc'],
						$DB['interface_id']['field_desc'],
						$DB['ip']['field_desc'],
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
		global $HOST;
		global $INTERFACE;
		$DB=$this->db_fields;

		$host_list=array();
		foreach($HOST->get_all() as $host):
			array_push($host_list, array($host['id'],$host['name']));
		endforeach;

		$interface_list=array();
		foreach($INTERFACE->get_all() as $interface):
			array_push($interface_list, array($interface['id'],$interface['name']." (".$interface['ip'].")"));
		endforeach;

		$fields=array(
			array(
				'field_name'	=>	'host_id',
				'field_desc'	=>	$DB['host_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$host_list,
				'required'		=>	$DB['host_id']['required'],
			),
			array(
				'field_name'	=>	'interface_id',
				'field_desc'	=>	$DB['interface_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$interface_list,
				'required'		=>	$DB['interface_id']['required'],
			),
			array(
				'field_name'	=>	'ip',
				'field_desc'	=>	$DB['ip']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['ip']['required'],
				'size'			=>	$DB['ip']['size'],
				'maxchar'		=>	$DB['ip']['maxchar']
			),
		);
		ita_register_script_formvalidator($this->menu_slug);
		//------------------------------------------------------------------------------------------------
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
		global $HOST;
		global $INTERFACE;
		$DB=$this->db_fields;
		
		$host_list=array();
		foreach($HOST->get_all() as $host):
			array_push($host_list, array($host['id'],$host['name']));
		endforeach;

		$interface_list=array();
		foreach($INTERFACE->get_all() as $interface):
			array_push($interface_list, array($interface['id'],$interface['name']));
		endforeach;

		$item=self::get_single($id);
		$fields=array(
			array(
				'field_name'	=>	'host_id',
				'field_desc'	=>	$DB['host_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['host_id'],				//en cainterface de dropdown
				'field_value'	=>	$host_list,
				'required'		=>	$DB['host_id']['required'],
			),
			array(
				'field_name'	=>	'interface_id',
				'field_desc'	=>	$DB['interface_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['interface_id'],				//en cainterface de dropdown
				'field_value'	=>	$interface_list,
				'required'		=>	$DB['interface_id']['required'],
			),
			array(
				'field_name'	=>	'ip',
				'field_desc'	=>	$DB['ip']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['ip'],
				'required'		=>	$DB['ip']['required'],
				'size'			=>	$DB['ip']['size'],
				'maxchar'		=>	$DB['ip']['maxchar']
			),
		);
		ita_register_script_formvalidator($this->menu_slug);
	
	
	
	
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

global $IP;
$IP =new IP_CLASS();
?>