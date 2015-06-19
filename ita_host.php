<?php
defined('ABSPATH') or die("No script kiddies please!");
class HOST_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'host';
		$this->name_single	= 'Host';
		$this->name_plural	= 'Hosts';
		$this->icon			= '<i class="fa fa-desktop"></i>';
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
									host_type_id tinyint unsigned not null,
									parent_id smallint unsigned null,
									so_id tinyint unsigned null,
									name varchar(40) not null,
									hostname varchar(30) not null,
									fqdn varchar(60) null,
									cpu float(4,1) null,
									ecpu float(4,1) null,
									vcpu float(4,1) null,
									ram smallint unsigned null,
									swap smallint unsigned null,
									sapsxcpu smallint unsigned null,
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
			'host_type_id'		=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Tipo de Host'),
			'parent_id'			=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Host Contenedor'),
			'so_id'				=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Sistema Operativo'),
			'name'				=> array('field_type'=>'text'		,'required'=>true,	'size'=>'M',	'maxchar'=>40,		'field_desc'=>'Nombre'),
			'hostname'			=> array('field_type'=>'text'		,'required'=>true,	'size'=>'XS',	'maxchar'=>30,		'field_desc'=>'Hostname'),
			'fqdn'				=> array('field_type'=>'text'		,'required'=>false,	'size'=>'L',	'maxchar'=>60,		'field_desc'=>'FQDN'),
			'cpu'				=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'CPU'),
			'ecpu'				=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'Entitled CPU'),
			'vcpu'				=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'Virtual CPU'),
			'ram'				=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'RAM'),
			'swap'				=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'SWAP'),
			'sapsxcpu'			=> array('field_type'=>'number'		,'required'=>false,	'size'=>'XS',	'maxchar'=>10,		'field_desc'=>'SAPs X CPU'),
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
		global $HOST_TYPE;
		global $SO;
		global $IP;
		$DB=$this->db_fields;
		//------------------------------------------------------------------------------------------------
		$sql="SELECT
				aa.id as id,
				aa.name as name,
				aa.hostname as hostname,
				bb.id as host_type_id,
				bb.name as host_type_name,
				bb.shortname as host_type_shortname,
				cc.id as parent_id,
				cc.name as parent_name,
				cc.hostname as parent_hostname,
				dd.id as so_id,
				dd.name as so_name,
				dd.shortname as so_shortname
			FROM
				(((($this->tbl_name as aa)
				LEFT JOIN $HOST_TYPE->tbl_name as bb ON aa.host_type_id=bb.id)
				LEFT JOIN $this->tbl_name as cc ON aa.parent_id=cc.id)
				LEFT JOIN $SO->tbl_name as dd ON aa.so_id=dd.id)
				";
		//funcion recursiva con par‡metros get
		$SQL_WHERE=array();
		if( isset($_GET['host_type_id']) && $_GET['host_type_id']!=0):
			array_push($SQL_WHERE,"bb.id=".$_GET['host_type_id']);
		endif;
		if( isset($_GET['parent_id']) && $_GET['parent_id']!=0):
			array_push($SQL_WHERE,"aa.parent_id=".$_GET['parent_id']);
		endif;
		if( isset($_GET['so_id']) && $_GET['so_id']!=0):
			array_push($SQL_WHERE,"dd.id=".$_GET['so_id']);
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
					$row['host_type_shortname'].'<br/><small>'.$row['host_type_name'].'</small>',
					$row['parent_name'].'<br/><small>'.$row['parent_name'].'</small>',
					$row['so_shortname'].'<br/><small>'.$row['so_name'].'</small>',
					$row['hostname'],
					$row['name'],
					"<small>".implode("<br/>",$wpdb->get_col($wpdb->prepare("SELECT ip FROM $IP->tbl_name WHERE host_id = %d", $row['id'] )))."</small>",
//					print_r($wpdb->get_col($wpdb->prepare("SELECT ip FROM $IP->tbl_name WHERE host_id = %d", $row['id'] ))),
					)
				);
		endforeach;
		//------------------------------------------------------------------------------------------------
		$host_type_filter=array();
		foreach($HOST_TYPE->get_all() as $host_type){
			array_push($host_type_filter,array($host_type['id'], $host_type['shortname']." (".$host_type['name'].")"));
		}
		$sql="SELECT bb.id,bb.name,bb.hostname
				FROM $this->tbl_name as aa
				LEFT OUTER JOIN $this->tbl_name as bb ON aa.parent_id=bb.id 
				WHERE aa.parent_id>0 
				GROUP BY bb.id";
		$parent_filter=array();
		foreach($HOST_TYPE->get_sql($sql) as $parent){
			array_push($parent_filter,array($parent['id'], $parent['hostname']." (".$parent['name'].")"));
		}
		$so_filter=array();
		foreach($SO->get_all() as $so){
			array_push($so_filter,array($so['id'], $so['shortname']." (".$so['name'].")"));
		}
		$filters=array(
			//array(filter_name			,filter_desc							,filter_type	,filter_values					,auto_change)
			array('host_type_id'		,$DB['host_type_id']['field_desc']		,'dropdown'		,$host_type_filter				,''	),
			array('parent_id'			,$DB['parent_id']['field_desc']			,'dropdown'		,$parent_filter					,''	),
			array('so_id'				,$DB['so_id']['field_desc']				,'dropdown'		,$so_filter						,''	),
			array('name'				,$DB['name']['field_desc']				,'text'			,''								,''	),
			array('hostname'			,$DB['hostname']['field_desc']			,'text'			,''								,''	),
			array('ip'					,"IPs"									,'text'			,''								,''	),
		);
		//------------------------------------------------------------------------------------------------
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array(
						'id',
						$DB['host_type_id']['field_desc'],
						$DB['parent_id']['field_desc'],
						$DB['so_id']['field_desc'],
						$DB['name']['field_desc'],
						$DB['hostname']['field_desc'],
						"IP",
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
		global $HOST_TYPE;
		global $SO;
		$DB=$this->db_fields;

		$host_type_list=array();
		foreach($HOST_TYPE->get_all() as $host_type):
			array_push($host_type_list, array($host_type['id'],$host_type['name']));
		endforeach;

		$parent_list=array();
		foreach($this->get_all() as $parent):
			array_push($parent_list, array($parent['id'],$parent['name']));
		endforeach;

		$so_list=array();
		foreach($SO->get_all() as $so):
			array_push($so_list, array($so['id'],$so['name']));
		endforeach;
		$fields=array(
			array(
				'field_name'	=>	'host_type_id',
				'field_desc'	=>	$DB['host_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$host_type_list,
				'required'		=>	$DB['host_type_id']['required'],
			),
			array(
				'field_name'	=>	'parent_id',
				'field_desc'	=>	$DB['parent_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$parent_list,
				'required'		=>	$DB['parent_id']['required'],
			),
			array(
				'field_name'	=>	'so_id',
				'field_desc'	=>	$DB['so_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$so_list,
				'required'		=>	$DB['so_id']['required'],
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
				'field_name'	=>	'hostname',
				'field_desc'	=>	$DB['hostname']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['hostname']['required'],
				'size'			=>	$DB['hostname']['size'],
				'maxchar'		=>	$DB['hostname']['maxchar']
			),
			array(
				'field_name'	=>	'fqdn',
				'field_desc'	=>	$DB['fqdn']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['fqdn']['required'],
				'size'			=>	$DB['fqdn']['size'],
				'maxchar'		=>	$DB['fqdn']['maxchar']
			),
			array(
				'field_name'	=>	'cpu',
				'field_desc'	=>	$DB['cpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['cpu']['required'],
				'size'			=>	$DB['cpu']['size'],
				'maxchar'		=>	$DB['cpu']['maxchar']
			),
			array(
				'field_name'	=>	'ecpu',
				'field_desc'	=>	$DB['ecpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['ecpu']['required'],
				'size'			=>	$DB['ecpu']['size'],
				'maxchar'		=>	$DB['ecpu']['maxchar']
			),
			array(
				'field_name'	=>	'vcpu',
				'field_desc'	=>	$DB['vcpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['vcpu']['required'],
				'size'			=>	$DB['vcpu']['size'],
				'maxchar'		=>	$DB['vcpu']['maxchar']
			),
			array(
				'field_name'	=>	'ram',
				'field_desc'	=>	$DB['ram']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['ram']['required'],
				'size'			=>	$DB['ram']['size'],
				'maxchar'		=>	$DB['ram']['maxchar']
			),
			array(
				'field_name'	=>	'swap',
				'field_desc'	=>	$DB['swap']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['swap']['required'],
				'size'			=>	$DB['swap']['size'],
				'maxchar'		=>	$DB['swap']['maxchar']
			),
			array(
				'field_name'	=>	'sapsxcpu',
				'field_desc'	=>	$DB['sapsxcpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	'',
				'required'		=>	$DB['sapsxcpu']['required'],
				'size'			=>	$DB['sapsxcpu']['size'],
				'maxchar'		=>	$DB['sapsxcpu']['maxchar']
			),
		);
		$formswitch_host=array();
		$properties=array("p_so_id","p_parent_id","p_name","p_hostname","p_fqdn","p_cpu","p_ecpu","p_vcpu","p_ram","p_swap","p_sapsxcpu");
		$host_type_list=array();
		foreach($HOST_TYPE->get_all() as $host_type):
			$val=null;
			$val=array();
			foreach($properties as $p):
				array_push($val,array(substr($p,2),$host_type[$p]));
			endforeach;
			array_push($formswitch_host,array($host_type['id'], $val) );
		endforeach;
		$formswitch_fields=array();
		foreach($properties as $p):
			array_push($formswitch_fields,substr($p,2));
		endforeach;
		//------------------------------------------------------------------------------------------------
		wp_register_script('ita_form_field_toggle',plugins_url( 'js/form_field_toggle.js' , __FILE__),array( 'jquery' ));
		wp_enqueue_script( 'ita_form_field_toggle');
		wp_localize_script('ita_form_field_toggle','ita_fft_vars',array(
				'plugin_post'	=>	$this->plugin_post,
				'switch_field'	=>	'#'.$this->plugin_post.'\\[host_type_id\\]',
				'fields'		=>	$formswitch_fields,
				'opts'			=>	$formswitch_host,
		));
		wp_register_script(
			'ita_custom_combobox',
			plugins_url( 'js/custom-combobox.js' , __FILE__),
			array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
			'1.0'
		);
		wp_enqueue_script( 'ita_custom_combobox');
		wp_localize_script('ita_custom_combobox','ita_ccb_vars',array(
				'input'			=>	'#'.$this->plugin_post.'\\[parent_id\\]',
		));
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
		global $HOST_TYPE;
		global $SO;
		$DB=$this->db_fields;
		
		$host_type_list=array();
		foreach($HOST_TYPE->get_all() as $host_type):
			array_push($host_type_list, array($host_type['id'],$host_type['name']));
		endforeach;

		$parent_list=array();
		foreach($this->get_all() as $parent):
			array_push($parent_list, array($parent['id'],$parent['name']));
		endforeach;

		$so_list=array();
		foreach($SO->get_all() as $so):
			array_push($so_list, array($so['id'],$so['name']));
		endforeach;
		$item=self::get_single($id);
		$fields=array(
			array(
				'field_name'	=>	'host_type_id',
				'field_desc'	=>	$DB['host_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['host_type_id'],				//en caso de dropdown
				'field_value'	=>	$host_type_list,
				'required'		=>	$DB['host_type_id']['required'],
			),
			array(
				'field_name'	=>	'parent_id',
				'field_desc'	=>	$DB['parent_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['parent_id'],				//en caso de dropdown
				'field_value'	=>	$parent_list,
				'required'		=>	$DB['parent_id']['required'],
			),
			array(
				'field_name'	=>	'so_id',
				'field_desc'	=>	$DB['so_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['so_id'],				//en caso de dropdown
				'field_value'	=>	$so_list,
				'required'		=>	$DB['so_id']['required'],
			),
			array(
				'field_name'	=>	'name',
				'field_desc'	=>	$DB['name']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['name'],
				'required'		=>	$DB['name']['required'],
				'size'			=>	$DB['name']['size'],
				'maxchar'		=>	$DB['name']['maxchar']
			),
			array(
				'field_name'	=>	'hostname',
				'field_desc'	=>	$DB['hostname']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['hostname'],
				'required'		=>	$DB['hostname']['required'],
				'size'			=>	$DB['hostname']['size'],
				'maxchar'		=>	$DB['hostname']['maxchar']
			),
			array(
				'field_name'	=>	'fqdn',
				'field_desc'	=>	$DB['fqdn']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['fqdn'],
				'required'		=>	$DB['fqdn']['required'],
				'size'			=>	$DB['fqdn']['size'],
				'maxchar'		=>	$DB['fqdn']['maxchar']
			),
			array(
				'field_name'	=>	'cpu',
				'field_desc'	=>	$DB['cpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['cpu'],
				'required'		=>	$DB['cpu']['required'],
				'size'			=>	$DB['cpu']['size'],
				'maxchar'		=>	$DB['cpu']['maxchar']
			),
			array(
				'field_name'	=>	'ecpu',
				'field_desc'	=>	$DB['ecpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['ecpu'],
				'required'		=>	$DB['ecpu']['required'],
				'size'			=>	$DB['ecpu']['size'],
				'maxchar'		=>	$DB['ecpu']['maxchar']
			),
			array(
				'field_name'	=>	'vcpu',
				'field_desc'	=>	$DB['vcpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['vcpu'],
				'required'		=>	$DB['vcpu']['required'],
				'size'			=>	$DB['vcpu']['size'],
				'maxchar'		=>	$DB['vcpu']['maxchar']
			),
			array(
				'field_name'	=>	'ram',
				'field_desc'	=>	$DB['ram']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['ram'],
				'required'		=>	$DB['ram']['required'],
				'size'			=>	$DB['ram']['size'],
				'maxchar'		=>	$DB['ram']['maxchar']
			),
			array(
				'field_name'	=>	'swap',
				'field_desc'	=>	$DB['swap']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['swap'],
				'required'		=>	$DB['swap']['required'],
				'size'			=>	$DB['swap']['size'],
				'maxchar'		=>	$DB['swap']['maxchar']
			),
			array(
				'field_name'	=>	'sapsxcpu',
				'field_desc'	=>	$DB['sapsxcpu']['field_desc'],
				'field_type'	=>	'text',
				'field_value'	=>	$item['sapsxcpu'],
				'required'		=>	$DB['sapsxcpu']['required'],
				'size'			=>	$DB['sapsxcpu']['size'],
				'maxchar'		=>	$DB['sapsxcpu']['maxchar']
			),
		);
		$formswitch_host=array();
		$properties=array("p_so_id","p_parent_id","p_name","p_hostname","p_fqdn","p_cpu","p_ecpu","p_vcpu","p_ram","p_swap","p_sapsxcpu");
		$host_type_list=array();
		foreach($HOST_TYPE->get_all() as $host_type):
			$val=null;
			$val=array();
			foreach($properties as $p):
				array_push($val,array(substr($p,2),$host_type[$p]));
			endforeach;
			array_push($formswitch_host,array($host_type['id'], $val) );
		endforeach;
		$formswitch_fields=array();
		foreach($properties as $p):
			array_push($formswitch_fields,substr($p,2));
		endforeach;

		
		wp_register_script('ita_form_field_toggle',plugins_url( 'js/form_field_toggle.js' , __FILE__),array( 'jquery' ));
		wp_enqueue_script( 'ita_form_field_toggle');
		wp_localize_script('ita_form_field_toggle','ita_fft_vars',array(
				'plugin_post'	=>	$this->plugin_post,
				'switch_field'	=>	'#'.$this->plugin_post.'\\[host_type_id\\]',
				'fields'		=>	$formswitch_fields,
				'opts'			=>	$formswitch_host,
		));

		wp_register_script(
			'ita_custom_combobox',
			plugins_url( 'js/custom-combobox.js' , __FILE__),
			array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
			'1.0'
		);
		wp_enqueue_script( 'ita_custom_combobox');
		wp_localize_script('ita_custom_combobox','ita_ccb_vars',array(
				'input'			=>	'#'.$this->plugin_post.'\\[parent_id\\]',
		));

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

global $HOST;
$HOST =new HOST_CLASS();
?>