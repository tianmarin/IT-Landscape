<?php
defined('ABSPATH') or die("No script kiddies please!");
class INSTANCE_CLASS extends ITA_CLASS{
	
	function __construct(){
		global $wpdb;
		global $ita_vars;
		$this->class_name	= 'instance';
		$this->name_single	= 'Instancia';
		$this->name_plural	= 'Instancias';
		$this->icon			= '<i class="fa fa-bars"></i>';
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
									instance_type_id tinyint unsigned not null,
									system_id tinyint unsigned not null,
									host_id tinyint unsigned not null,
									kernel_release_id tinyint unsigned null,
									inst_no tinyint null,
									inst_order tinyint unsigned not null,
									description varchar(255) null,
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
			//field_name			field_type						required			form_size		maxchar				desc
			'id'				=> array('field_type'=>'id'			,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'id'),
			'instance_type_id'	=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Tipo de Instancia'),
			'system_id'			=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Sistema'),
			'host_id'			=> array('field_type'=>'nat_number'	,'required'=>true,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Host'),
			'kernel_release_id'	=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>null,	'field_desc'=>'Release de Kernel'),
			'inst_no'			=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>2,		'field_desc'=>'Inst. No.'),
			'inst_order'		=> array('field_type'=>'nat_number'	,'required'=>false,	'size'=>'XS',	'maxchar'=>2,		'field_desc'=>'Orden Reinicio'),
			'description'		=> array('field_type'=>'textarea'	,'required'=>false,	'size'=>'L',	'maxchar'=>255,		'field_desc'=>'Descripci&oacute;n'),
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
		global $INSTANCE_TYPE;
		global $SYSTEM;
		global $LANDSCAPE;
		global $HOST;
		global $KERNEL_RELEASE;
		$DB=$this->db_fields;
		//------------------------------------------------------------------------------------------------
		$sql="SELECT
				aa.id as id,
				aa.inst_no as inst_no,
				bb.id as instance_type_id,
				bb.name as instance_type_name,
				bb.shortname as instance_type_shortname,
				cc.id as system_id,
				cc.sid as system_sid,
				dd.id as landscape_id,
				dd.name as landscape_name,
				dd.shortname as landscape_shortname,
				ee.id as host_id,
				ee.name as host_name,
				ee.hostname as host_hosttname,
				ff.id as kernel_release_id,
				ff.name as kernel_release_name,
				ff.shortname as kernel_release_shortname
			FROM
				(((((($this->tbl_name as aa)
				LEFT JOIN $INSTANCE_TYPE->tbl_name as bb ON aa.instance_type_id=bb.id)
				LEFT JOIN $SYSTEM->tbl_name as cc ON aa.system_id=cc.id)
				LEFT JOIN $LANDSCAPE->tbl_name as dd ON cc.landscape_id=dd.id)
				LEFT JOIN $HOST->tbl_name as ee ON aa.host_id=ee.id)
				LEFT JOIN $KERNEL_RELEASE->tbl_name as ff ON aa.kernel_release_id=ff.id)
				";
//		print_r($sql);
		$SQL_WHERE=array();
		if( isset($_GET['instance_type_id']) && $_GET['instance_type_id']!=0):
			array_push($SQL_WHERE,"bb.id=".$_GET['instance_type_id']);
		endif;
		if( isset($_GET['system_id']) && $_GET['system_id']!=0):
			array_push($SQL_WHERE,"cc.id=".$_GET['system_id']);
		endif;
		if( isset($_GET['landscape_id']) && $_GET['landscape_id']!=0):
			array_push($SQL_WHERE,"dd.id=".$_GET['landscape_id']);
		endif;
		if( isset($_GET['host_id']) && $_GET['host_id']!=0):
			array_push($SQL_WHERE,"ee.id=".$_GET['host_id']);
		endif;
		if( isset($_GET['kernel_release_id']) && $_GET['kernel_release_id']!=0):
			array_push($SQL_WHERE,"ff.id=".$_GET['kernel_release_id']);
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
					$row['system_sid'],
					$row['instance_type_shortname'].'<br/><small>'.$row['instance_type_name'].'</small>',
					$row['host_name'].'<br/><small>'.$row['host_name'].'</small>',
					$row['kernel_release_name'].'<br/><small>'.$row['kernel_release_name'].'</small>',
					sprintf("%02d", $row['inst_no']),
					)
				);
		endforeach;
		//------------------------------------------------------------------------------------------------
		$instance_type_filter=array();
		foreach($INSTANCE_TYPE->get_all() as $instance_type){
			array_push($instance_type_filter,array($instance_type['id'], $instance_type['shortname']." (".$instance_type['name'].")"));
		}
		$system_filter=array();
		foreach($SYSTEM->get_all() as $system){
			array_push($system_filter,array($system['id'], $system['sid']));
		}
		$landscape_filter=array();
		foreach($LANDSCAPE->get_all() as $landscape){
			array_push($landscape_filter,array($landscape['id'], $landscape['shortname']." (".$landscape['name'].")"));
		}
		$host_filter=array();
		foreach($HOST->get_all() as $host){
			array_push($host_filter,array($host['id'], $host['hostname']." (".$host['name'].")"));
		}
		$kernel_release_filter=array();
		foreach($KERNEL_RELEASE->get_all() as $kernel_release){
			array_push($kernel_release_filter,array($kernel_release['id'], $kernel_release['shortname']." (".$kernel_release['name'].")"));
		}
		$filters=array(
			//array(filter_name			,filter_desc							,filter_type	,filter_values					,auto_change)
			array('landscape_id'		,"Landscape"							,'dropdown'		,$landscape_filter				,''	),
			array('system_id'			,$DB['system_id']['field_desc']			,'dropdown'		,$system_filter					,''	),
			array('instance_type_id'	,$DB['instance_type_id']['field_desc']	,'dropdown'		,$instance_type_filter			,''	),
			array('host_id'				,$DB['host_id']['field_desc']			,'dropdown'		,$host_filter					,''	),
			array('kernel_release_id'	,$DB['kernel_release_id']['field_desc']	,'dropdown'		,$kernel_release_filter			,''	),
			array('inst_no'				,$DB['inst_no']['field_desc']			,'text'			,''								,''	),
		);
		//------------------------------------------------------------------------------------------------
		$title		= "Listado de ".$this->name_plural;
		$add_new	= "Agregar nuevo";
		$titles		= array(
						'id',
						"Landscape",
						$DB['system_id']['field_desc'],
						$DB['instance_type_id']['field_desc'],
						$DB['host_id']['field_desc'],
						$DB['kernel_release_id']['field_desc'],
						$DB['inst_no']['field_desc'],
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
	* Form to add a single Class registry to the instance
	*
	* @since 1.0
	* @author Cristian Marin
	*/
	public function new_form(){
		global $INSTANCE_TYPE;
		global $SYSTEM;
		global $LANDSCAPE;
		global $HOST;
		global $KERNEL_RELEASE;
		$DB=$this->db_fields;

		$instance_type_list=array();
		foreach($INSTANCE_TYPE->get_all() as $instance_type):
			array_push($instance_type_list, array($instance_type['id'],$instance_type['name']));
		endforeach;

		$landscape_list=array();
		foreach($LANDSCAPE->get_all() as $landscape):
			array_push($landscape_list, array($landscape['id'],$landscape['name']));
		endforeach;


		$system_list=array();
		foreach($SYSTEM->get_all() as $system):
			array_push($system_list, array($system['id'],$system['sid']));
		endforeach;

		$host_list=array();
		foreach($HOST->get_all() as $host):
			array_push($host_list, array($host['id'],$host['hostname']));
		endforeach;

		$kernel_release_list=array();
		foreach($KERNEL_RELEASE->get_all() as $kernel_release):
			array_push($kernel_release_list, array($kernel_release['id'],$kernel_release['name']));
		endforeach;

		$fields=array(
			array(
				'field_name'	=>	'landscape_id',
				'field_desc'	=>	"Landscape",
				'field_type'	=>	'dropdown',
				'field_value'	=>	$landscape_list,
				'required'		=>	null,
			),
			array(
				'field_name'	=>	'system_id',
				'field_desc'	=>	$DB['system_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	'',
				'required'		=>	$DB['system_id']['required'],
			),
			array(
				'field_name'	=>	'instance_type_id',
				'field_desc'	=>	$DB['instance_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$instance_type_list,
				'required'		=>	$DB['instance_type_id']['required'],
			),
			array(
				'field_name'	=>	'host_id',
				'field_desc'	=>	$DB['host_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$host_list,
				'required'		=>	$DB['host_id']['required'],
			),
			array(
				'field_name'	=>	'kernel_release_id',
				'field_desc'	=>	$DB['kernel_release_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'field_value'	=>	$kernel_release_list,
				'required'		=>	$DB['kernel_release_id']['required'],
			),
			array(
				'field_name'	=>	'inst_no',
				'field_desc'	=>	$DB['inst_no']['field_desc'],
				'field_type'	=>	$DB['inst_no']['field_type'],
				'field_value'	=>	'',
				'required'		=>	$DB['inst_no']['required'],
				'size'			=>	$DB['inst_no']['size'],
				'maxchar'		=>	$DB['inst_no']['maxchar']
			),
			array(
				'field_name'	=>	'inst_order',
				'field_desc'	=>	$DB['inst_order']['field_desc'],
				'field_type'	=>	$DB['inst_order']['field_type'],
				'field_value'	=>	'',
				'required'		=>	$DB['inst_order']['required'],
				'size'			=>	$DB['inst_order']['size'],
				'maxchar'		=>	$DB['inst_order']['maxchar']
			),
			array(
				'field_name'	=>	'description',
				'field_desc'	=>	$DB['description']['field_desc'],
				'field_type'	=>	'textarea',
				'field_value'	=>	'',
				'required'		=>	$DB['description']['required'],
				'size'			=>	$DB['description']['size'],
				'maxchar'		=>	$DB['description']['maxchar']
			),
		);
		$this->ita_register_ajaxmethods_scripts();
		add_action( 'wp_ajax_ita_select_get_system_by_landscape', array($this,'ita_select_get_system_by_landscape') );

		wp_register_script(
			'ita_custom_combobox',
			plugins_url( 'js/custom-combobox.js' , __FILE__),
			array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
			'1.0'
		);
		wp_enqueue_script( 'ita_custom_combobox');
		wp_localize_script('ita_custom_combobox','ita_ccb_vars',array(
				'input'			=>	'#'.$this->plugin_post.'\\[host_id\\]',
		));

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
	* Form to edit a single Class registry to the instance
	*
	* @since 1.0
	* @author Cristian Marin
	*/
	function edit_form($id=0){
		global $wpdb;
		global $LANDSCAPE;
		global $INSTANCE_TYPE;
		global $SYSTEM;
		global $HOST;
		global $KERNEL_RELEASE;
		$DB=$this->db_fields;

		$landscape_list=array();
		foreach($LANDSCAPE->get_all() as $landscape):
			array_push($landscape_list, array($landscape['id'],$landscape['name']));
		endforeach;

		$instance_type_list=array();
		foreach($INSTANCE_TYPE->get_all() as $instance_type):
			array_push($instance_type_list, array($instance_type['id'],$instance_type['name']));
		endforeach;

		$system_list=array();
		foreach($SYSTEM->get_all() as $system):
			array_push($system_list, array($system['id'],$system['sid']));
		endforeach;

		$host_list=array();
		foreach($HOST->get_all() as $host):
			array_push($host_list, array($host['id'],$host['name']));
		endforeach;

		$kernel_release_list=array();
		foreach($KERNEL_RELEASE->get_all() as $kernel_release):
			array_push($kernel_release_list, array($kernel_release['id'],$kernel_release['name']));
		endforeach;

		$item=self::get_single($id);
		$sql="SELECT bb.id FROM ($SYSTEM->tbl_name as aa) INNER JOIN $LANDSCAPE->tbl_name as bb ON aa.landscape_id=bb.id WHERE aa.id=%d";
		$landscape=$wpdb->get_row($wpdb->prepare($sql, $item['system_id'] ),ARRAY_A);
		$fields=array(
			array(
				'field_name'	=>	'landscape_id',
				'field_desc'	=>	"Landscape",
				'field_type'	=>	'dropdown',
				'selected'		=>	$landscape['id'],				//en caso de dropdown
				'field_value'	=>	$landscape_list,
				'required'		=>	null,
			),
			array(
				'field_name'	=>	'instance_type_id',
				'field_desc'	=>	$DB['instance_type_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['instance_type_id'],				//en caso de dropdown
				'field_value'	=>	$instance_type_list,
				'required'		=>	$DB['instance_type_id']['required'],
			),
			array(
				'field_name'	=>	'system_id',
				'field_desc'	=>	$DB['system_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['system_id'],				//en caso de dropdown
				'field_value'	=>	$system_list,
				'required'		=>	$DB['system_id']['required'],
			),
			array(
				'field_name'	=>	'host_id',
				'field_desc'	=>	$DB['host_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['host_id'],				//en caso de dropdown
				'field_value'	=>	$host_list,
				'required'		=>	$DB['host_id']['required'],
			),
			array(
				'field_name'	=>	'kernel_release_id',
				'field_desc'	=>	$DB['kernel_release_id']['field_desc'],
				'field_type'	=>	'dropdown',
				'selected'		=>	$item['kernel_release_id'],				//en caso de dropdown
				'field_value'	=>	$kernel_release_list,
				'required'		=>	$DB['kernel_release_id']['required'],
			),
			array(
				'field_name'	=>	'inst_no',
				'field_desc'	=>	$DB['inst_no']['field_desc'],
				'field_type'	=>	$DB['inst_no']['field_type'],
				'field_value'	=>	$item['inst_no'],
				'required'		=>	$DB['inst_no']['required'],
				'size'			=>	$DB['inst_no']['size'],
				'maxchar'		=>	$DB['inst_no']['maxchar']
			),
			array(
				'field_name'	=>	'inst_order',
				'field_desc'	=>	$DB['inst_order']['field_desc'],
				'field_type'	=>	$DB['inst_order']['field_type'],
				'field_value'	=>	$item['inst_order'],
				'required'		=>	$DB['inst_order']['required'],
				'size'			=>	$DB['inst_order']['size'],
				'maxchar'		=>	$DB['inst_order']['maxchar']
			),
			array(
				'field_name'	=>	'description',
				'field_desc'	=>	$DB['description']['field_desc'],
				'field_type'	=>	'textarea',
				'field_value'	=>	$item['description'],
				'required'		=>	$DB['description']['required'],
				'size'			=>	$DB['description']['size'],
				'maxchar'		=>	$DB['description']['maxchar']
			),
		);
	
		$this->ita_register_ajaxmethods_scripts();
		add_action( 'wp_ajax_ita_select_get_system_by_landscape', array($this,'ita_select_get_system_by_landscape') );

		wp_register_script(
			'ita_custom_combobox',
			plugins_url( 'js/custom-combobox.js' , __FILE__),
			array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
			'1.0'
		);
		wp_enqueue_script( 'ita_custom_combobox');
		wp_localize_script('ita_custom_combobox','ita_ccb_vars',array(
				'input'			=>	'#'.$this->plugin_post.'\\[host_id\\]',
		));

		ita_register_script_formvalidator($this->menu_slug);
	
//------------	
		$title="Editar ".$this->name_single;
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
/**
* Register, Enqueue & localiza specific plugin ajax script
*/
function ita_register_ajaxmethods_scripts() {
	wp_register_script(
		'ita_ajax_instance',
		plugins_url( 'js/ajax_instance.js' , __FILE__),
		array('jquery', 'jquery-ui-core','jquery-ui-autocomplete','jquery-ui-tooltip','jquery-ui-button'),
		'1.0'
	);
	wp_enqueue_script(	'ita_ajax_instance');
	wp_localize_script(
		'ita_ajax_instance',
		'ita_ajax',
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
	);
}

//------------------------------------------------------------------------------------------------------------------------------------

public function ita_select_get_system_by_landscape(){
	global $SYSTEM;
	if( isset( $_POST['landscape_id'] ) && is_numeric( $_POST['landscape_id'] ) ) {	
		$sql="SELECT id,sid FROM $SYSTEM->tbl_name WHERE landscape_id=".$_POST['landscape_id'];
		$systems= $SYSTEM->get_sql($sql);
		echo '<option value="0">Selecciona '.$SYSTEM->name_single.'</option>';
		foreach($systems as $system){
			$option = '<option value="'.$system['id'].'">'.$system['sid'].'</option>';
			echo $option;
		}
	}	
	die();
}

//END OF CLASS	
}

global $INSTANCE;
$INSTANCE =new INSTANCE_CLASS();
?>