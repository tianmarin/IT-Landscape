<?php
defined('ABSPATH') or die("No script kiddies please!");

//---------------------------------------------------------------------------------------------------------------------------------------------------------
/**
* Process the plugin's shortcode when it is called and return the results
*
* @author Cristian Marin
*/
function ita_handle_shortcode($atts){
	global $SYSTEM;
	global $LANDSCAPE;
	if(isset($atts['restart']) && $atts['system']!=0){
		return system_restart_shortcode($atts['system'],isset($atts['type'])?$atts['type']:null);
	}elseif(isset($atts['system']) && $atts['system']!=0){
		if(isset($atts['deep'])){
			return system_shortcode($atts['system'], isset($atts['deep'])?$atts['deep']:null, true);
		}
	}elseif(isset($atts['landscape']) && $atts['landscape']!=0){
		return landscape_shortcode($atts['landscape']);
	}elseif(isset($atts['instance']) && $atts['instance']!=0){
		return $INSTANCE->class_shortcode($atts['instance']);
	}
}

add_shortcode( $ita_vars['plugin_shortcode'], 'ita_handle_shortcode' );
//---------------------------------------------------------------------------------------------------------------------------------------------------------
	/**
	* Shows a single Class registry from the database styled for easy visualization
	*
	* @since 1.0
	* @author Cristian Marin
	*/
	function landscape_shortcode( $id = 0 ){
		global $LANDSCAPE;
		global $SYSTEM;
		global $SYSTEM_TYPE;
		global $ENVIRONMENT;
		global $PRODUCT;
		
	
	
		wp_register_style(
			"ita_shortcode_style",
			plugins_url( 'css/shortcode.css' , __FILE__ ),
			null,
			"1.0",
			"all"
			);
		wp_enqueue_style( "ita_shortcode_style" );
		$output= '<div class="ita_landscape">';
		$landscape=$LANDSCAPE->get_single($id);
		$output.='<div class="title">'.$LANDSCAPE->icon.' '.$landscape['name'].'</div>';
		if($id==0):
			$output.= '<div class="error">';
			$output.= '<b>ÁERROR!</b><br>Valida la secuencia que has escrito este correcta.';
			$output.= '</div>';
		else:
			$sql="SELECT
				aa.name as landscape_name,
				cc.id as id,
				cc.name as name,
				cc.shortname as shortname
			FROM
				((($LANDSCAPE->tbl_name as aa)
				INNER JOIN $SYSTEM->tbl_name as bb ON bb.landscape_id=aa.id)
				INNER JOIN $SYSTEM_TYPE->tbl_name as cc ON bb.system_type_id=cc.id)
			WHERE
				aa.id=$id
			GROUP BY
				cc.id
			ORDER BY
				cc.name
				";
			$system_types = $LANDSCAPE->get_sql($sql);
			foreach($system_types as $system_type):
				$output.='<div class="system_type">';
					$output.='<header><span class="title">'.$SYSTEM_TYPE->icon.' '.$system_type['name'].' <small>('.$system_type['shortname'].')</small></span></header>';
					$output.='<div class="systems">';
					$system_sql="SELECT
							bb.sid as sid,
							dd.name as environment_name,
							ee.name as product_name
						FROM
							((((($LANDSCAPE->tbl_name as aa)
							INNER JOIN $SYSTEM->tbl_name as bb ON bb.landscape_id=aa.id)
							INNER JOIN $SYSTEM_TYPE->tbl_name as cc ON bb.system_type_id=cc.id)
							INNER JOIN $ENVIRONMENT->tbl_name as dd ON bb.environment_id=dd.id)
							INNER JOIN $PRODUCT->tbl_name as ee ON bb.product_id=ee.id)
						WHERE
							aa.id=$id AND cc.id=".$system_type['id']."
						ORDER BY
							dd.env_order
						";
				$systems = $LANDSCAPE->get_sql($system_sql);
				foreach($systems as $system):
					$output.= '<div class="system">';
						$st_icon="";
						foreach(explode(" ",$system_type['shortname']) as $st_word):
							$st_icon.=substr($st_word,0,1);
						endforeach;
						$output.='<div class="st_icon st_'.$system_type['id'].'">'.$st_icon.'</div>';
						$output.= '<span>'.$SYSTEM->icon.' '.$system['sid'].'</span>';
						$output.= '<span><small>'.$ENVIRONMENT->icon.' '.$system['environment_name'].'</small></span>';
						$output.= '<span><small>'.$PRODUCT->icon.' '.$system['product_name'].'</small></span>';
					$output.= '</div>';
				endforeach;
				$output.= '</div>';
				$output.= '</div>';
			endforeach;
		endif;
		$output.= '</div>';
		return $output;
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------------
/**
* Shows a single Class registry from the database styled for easy visualization
*
* @since 1.0
* @author Cristian Marin
*/
function system_shortcode( $id = 0 , $deep=1, $title_flag=null){
	global $wpdb;
	global $LANDSCAPE;
	global $SYSTEM;
	global $HOST;
	global $INSTANCE;
	global $INSTANCE_TYPE;
	global $KERNEL_RELEASE;
		wp_register_style(
			"ita_shortcode_style",
			plugins_url( 'css/shortcode.css' , __FILE__ ),
			null,
			"1.0",
			"all"
			);
		wp_enqueue_style( "ita_shortcode_style" );

	$hosts_a=array();
	$output='<div class="ita_system">';
	$sql="SELECT
			aa.sid as sid,
			bb.shortname as landscape_shortname,
			bb.name as landscape_name
		FROM
			(($SYSTEM->tbl_name as aa)
			LEFT JOIN $LANDSCAPE->tbl_name as bb ON aa.landscape_id=bb.id)
			WHERE aa.id=%d
			";
	$system=$wpdb->get_row($wpdb->prepare($sql, $id ),ARRAY_A);
	if($title_flag!=null):
		$output.="<div class='title'>".$SYSTEM->icon.' '.$system['landscape_name']." &gt; ".$system['sid']."</div> ";
	endif;
	//Get Server List
	//Profundidad 1 by default
	$sql="SELECT
			cc.id as id,
			cc.hostname as hostname,
			cc.parent_id as parent_id
		FROM
			((($SYSTEM->tbl_name as aa)
			LEFT JOIN $INSTANCE->tbl_name as bb ON bb.system_id=aa.id)
			LEFT JOIN $HOST->tbl_name as cc ON bb.host_id=cc.id)
		WHERE aa.id=$id
		GROUP BY
			cc.id
	";
	$hosts=$HOST->get_sql($sql);
	if($wpdb->num_rows>0):
		$hosts_a[$deep]=array();
		foreach($hosts as $host):
			array_push($hosts_a[$deep],$host['id']);
		endforeach;
		for($i=$deep-1;$i>0;$i--){
			$sql="SELECT bb.id
				FROM 
					(($HOST->tbl_name as aa) INNER JOIN $HOST->tbl_name as bb ON aa.parent_id=bb.id)
				WHERE
					aa.id=".implode(" OR aa.id=",$hosts_a[$i+1])."
				GROUP BY
					bb.id";
			$parent_hosts=$HOST->get_sql($sql);
			if($parent_hosts==null)
				break;
			$hosts_a[$i]=array();
			foreach($parent_hosts as $host):
				array_push($hosts_a[$i],$host['id']);
			endforeach;
		}
		$output.=ita_print_hosts($hosts_a,$i+1,$deep,$id,null);
	else:
		$output.='<span class="error">'.'No se han definido instancias para el sistema: '.$system['sid'].' (id:'.$id.')</span>';
	endif;
	$output.= '</div>';
	return $output;
}

function ita_print_hosts($hosts_a,$i,$deep,$system_id,$parent_id){
	global $SYSTEM;
	global $HOST;
	global $SO;
	global $INSTANCE;
	global $INSTANCE_TYPE;
	global $KERNEL_RELEASE;
	global $IP;
	global $INTERFACE;
	
	$output='';
	if(array_key_exists($i,$hosts_a) || $i<=$deep):
		if($parent_id==null){
			$sql="SELECT aa.id as id
				FROM
					($HOST->tbl_name as aa)
				WHERE
					(aa.id=".implode(" OR aa.id=",$hosts_a[$i]).")";
			$hosts=$HOST->get_sql($sql);
		}else{
			$sql="SELECT aa.id as id
				FROM
					($HOST->tbl_name as aa)
				WHERE
					(aa.id=".implode(" OR aa.id=",$hosts_a[$i]).")
					AND aa.parent_id=".$parent_id;
			$hosts=$HOST->get_sql($sql);
		}
		foreach($hosts as $host):
//			$host_sql="SELECT aa.id FROM (($HOST->tbl_name as aa) LEFT JOIN $SO->tbl_name as bb ON aa.so_id=aa.id) WHERE aa.id=%d"
//			$host=$wpdb->get_row($wpdb->prepare($host_sql, $host),ARRAY_A);

			$host=$HOST->get_single($host);
			$so=$SO->get_single($host['so_id']);
			$output.='<div class="host">';
			$output.='<span class="host_info">';
			$output.='<div>'.$HOST->icon.' '.$host['hostname'].'</div>';
			$ips="SELECT 
					aa.name as name,
					aa.shortname as shortname,
					bb.ip as ip
				FROM
					(($INTERFACE->tbl_name as aa)
					LEFT JOIN $IP->tbl_name as bb ON bb.interface_id=aa.id)
				WHERE
					bb.host_id=".$host['id'];
			$output.="<ul>";
				$output.=($host['fqdn']!='')?'<li><i class="fa fa-comment"></i> '.$host['fqdn'].'</li>':'';
				$output.=($so['shortname']!='')?'<li>'.$SO->icon.' '.$so['shortname'].'</li>':'';
				$output.=($host['ram']!=0)?'<li><i class="fa fa-area-chart"></i> RAM: '.$host['ram'].'</li>':'';
				$output.=($host['cpu']!=0)?'<li><i class="fa fa-area-chart"></i> CPU: '.$host['cpu'].'</li>':'';
				$output.=($host['ecpu']!=0)?'<li><i class="fa fa-area-chart"></i> eCPU: '.$host['ecpu'].'</li>':'';
				$output.=($host['vcpu']!=0)?'<li><i class="fa fa-area-chart"></i> vCPU: '.$host['vcpu'].'</li>':'';
				$output.=($host['swap']!=0)?'<li><i class="fa fa-area-chart"></i> SWAP: '.$host['swap'].'</li>':'';
				$output.=($host['sapsxcpu']!=0)?'<li><i class="fa fa-area-chart"></i> SAPSxCPU: '.$host['sapsxcpu'].'</li>':'';
				$output.="<hr/>";
				foreach($IP->get_sql($ips) as $ip):
					$output.='<li class="ip">'.$IP->icon.' '.$ip['shortname'].' : '.$ip['ip'].'</li>';
				endforeach;
			$output.= '</ul>';
			$output.= '</span><!-- FIN .host_info -->';
			
			$output.=ita_print_hosts($hosts_a,$i+1,$deep,$system_id,$host['id']);	
//			if($parent_id!=null):
				$sql="SELECT
						aa.description as instance_description,
						aa.inst_no as inst_no,
						bb.name as instance_type_name,
						bb.shortname as instance_type_shortname,
						cc.name as kernel_release_shortname
					FROM
						((((($INSTANCE->tbl_name as aa)
						LEFT JOIN $INSTANCE_TYPE->tbl_name as bb ON aa.instance_type_id=bb.id)
						LEFT JOIN $KERNEL_RELEASE->tbl_name as cc ON aa.kernel_release_id=cc.id)
						LEFT JOIN $SYSTEM->tbl_name as dd ON aa.system_id=dd.id)
						LEFT JOIN $HOST->tbl_name as ee ON aa.host_id=ee.id)
					WHERE
						aa.system_id=$system_id AND
						aa.host_id=".$host['id'];
				if($parent_id!=null):
					$sql.=" AND
						ee.parent_id=".$parent_id."
						";
				endif;
				foreach($INSTANCE->get_sql($sql) as $instance):
					$output.='<div class="instance">';
						$output.= '<div class="instance_type_shortname">'.$INSTANCE->icon.' '.$instance['instance_type_shortname'].'</div>';
						$output.="<ul>";
							$output.= '<li class="instance_type_name">('.$instance['instance_type_name'].')</li>';
							if($instance['inst_no'] != null):
								$output.= '<li class="instance_description">Inst. No.: '.sprintf("%02d", $instance['inst_no']).'</li>';
							endif;
							if($instance['kernel_release_shortname'] != ''):
								$output.= '<li class="kernel_release_shortname">'.$KERNEL_RELEASE->icon.' '.$instance['kernel_release_shortname'].'</li>';
							endif;
							if($instance['instance_description'] != ''):
								$output.= '<li class="instance_description">'.$instance['instance_description'].'</li>';
							endif;
						$output.="</ul>";
					$output.= '</div><!-- FIN .instance -->';
				endforeach;
//			endif;
			$output.= '</div>';
		endforeach;
		return $output;
	endif;
	return null;
}
/*
foreach($HOST_DE_SISTEMA as $host){
	

*/

//---------------------------------------------------------------------------------------------------------------------------------------------------------
/**
* Shows a single System Procedure for restart systems
*
* @since 1.0
* @author Cristian Marin
*/
function system_restart_shortcode( $id = 0, $sys_type=null){
	global $wpdb;
	global $LANDSCAPE;
	global $SYSTEM;
	global $ENVIRONMENT;
	global $HOST;
	global $INSTANCE;
	global $INSTANCE_TYPE;
	global $KERNEL_RELEASE;
	
		wp_register_style(
			"ita_shortcode_style",
			plugins_url( 'css/shortcode.css' , __FILE__ ),
			null,
			"1.0",
			"all"
			);
		wp_enqueue_style( "ita_shortcode_style" );

	$sql="SELECT
			aa.sid as sid,
			bb.name as environment,
			bb.name as landscape_name
		FROM
			(($SYSTEM->tbl_name as aa)
			LEFT JOIN $ENVIRONMENT->tbl_name as bb ON aa.environment_id=bb.id)
			WHERE aa.id=%d
			";
	$system=$wpdb->get_row($wpdb->prepare($sql, $id ),ARRAY_A);
	$sql_2="SELECT
			cc.hostname as hostname,
			bb.inst_no as inst_no
		FROM
			((($SYSTEM->tbl_name as aa)
			LEFT JOIN $INSTANCE->tbl_name as bb ON bb.system_id = aa.id)
			LEFT JOIN $HOST->tbl_name as cc ON bb.host_id = cc.id)
		WHERE
			aa.id=$id AND
			bb.inst_order>0
		ORDER BY
			bb.inst_order DESC
		";
	$instances=$INSTANCE->get_sql($sql_2);
	$output='<h1>Detener Sistema '.$system['environment']." ".$system['sid']."</h1>";
	//---------------------------------------------------------------------------
	$output.='<h2>Detenci&oacute;n de Aplicaci&oacute;n SAP</h2>';
	$output.="<blockquote>";
		$output.="Con el usuario <em>".strtolower($system['sid'])."adm</em> se deben ejecutar los comandos en el siguiente orden:";
		$output.="<pre>";
			foreach($instances as $instance):
				$output.="stopsap ".$instance['hostname']."<br/>";
			endforeach;
		$output.="</pre>";		
	$output.="</blockquote>";
	//---------------------------------------------------------------------------
	$output.='<h2>Detenci&oacute;n de Base de Datos</h2>';
	$output.="<blockquote>";
		$output.="Validar si la DB fue bajada autom&acute;ticamente con el siguiente comando (debe ser ejecutado con el usuario <em>db2".strtolower($system['sid'])."</em>):";
		$output.="<pre>db2 list applications</pre>";
		$output.="La salida del comando deber&iacute;a ser:";
		$output.="<pre>SQL1032N  No start database manager command was issued.  SQLSTATE=57019</pre>";
		$output.="Para bajar la Base de Datos de modo Manual (al bajar SAP la DB debe bajar de modo automatico), con el usuario <em>db2".strtolower($system['sid'])."</em> se ejecuta el siguiente comando:";
		$output.="<pre>db2stop</pre>";
	$output.="</blockquote>";
	//---------------------------------------------------------------------------
	$output.='<h2>Detenci&oacute;n de Servicios/Deamons SAP</h2>';
	$output.="<blockquote>";
		$output.="En el caso que el reinicio se ejecute por actividad de Actualizaci&oacute;n de Kernel u otro proceso que implique reinicio completo de la aplicaci&oacute;n, se deben dar de baja los agentes con los siguientes comandos:";
		$output.="<pre>";
			foreach($instances as $instance):
				$output.="sapcontrol -nr ".sprintf("%02d", $instance['inst_no'])." -function StopService<br/>";
			endforeach;
		$output.="</pre>";		
	$output.="</blockquote>";
	//---------------------------------------------------------------------------
	$output.='<h2>Limpieza de Segmentos de Memoria</h2>';
	$output.='<ul><strong>Nota</strong>:<br/>Si en un mismo servidor existe m&aacute;s de una instancia (por ejemplo: ASCS y CI) <strong>S&Oacute;LO</strong> se deben limpiar los segmentos de memoria si el reinicio del sistema es completo (es decir, si una instancia no se est&aacute; dando de baja y comparte servidor con otra instancia del mismo sistema, no se deben limpiar los segmentos de memoria en dicho servidor)</ul>'; 
	$output.="<blockquote>";
		$output.="Posterior a la detenci&oacute;n del sistema es necesario ejecutar la limpieza de los segmentos y sem&aacute;foros de memoria con los siguientes comandos (debe ser ejecutado con el usuario <em>".strtolower($system['sid'])."adm</em>):";
		$output.="<pre>";
			$output.="ipcs | grep ".strtolower($system['sid'])."adm | awk '{print $1,$2,$3}' | awk '{print \"ipcrm -m \"$2}' | sh"."<br/>";
			$output.="ipcs | grep ".strtolower($system['sid'])."adm | awk '{print $1,$2,$3}' | awk '{print \"ipcrm -s \"$2}' | sh";
		$output.="</pre>";
	$output.="</blockquote>";
	$output.="<blockquote>";
		$output.="<strong>Nota</strong>:<br/>";
		$output.="Si el sistema es <strong>JAVA</strong> o <strong>ABAP+JAVA</strong>, es necesario realizar la limpieza de la Shared Memory; ejecutar con el usuario <em>".strtolower($system['sid'])."adm</em> el siguiente comando:";
		$output.="<pre>";
			$output.="jcontrol pf=/sapmnt/".$system['sid']."/profile/&lt;CENTRAL_INSTANCE_PROFILE&gt; -c"."<br/>";
		$output.="</pre>";
	$output.="</blockquote>";
	
	
	$instances=array_reverse($instances);
	$output.="<hr/>";
	$output.='<h1>Iniciar Sistema '.$system['environment']." ".$system['sid']."</h1>";
	//---------------------------------------------------------------------------
	$output.='<h2>Inicio de Servicios/Deamons SAP</h2>';
	$output.="<blockquote>";
		$output.="En el caso que el reinicio se ejecute por actividad de Actualizaci&oacute;n de Kernel u otro proceso que implique reinicio completo de la aplicaci&oacute;n, se deben dar de baja los agentes con los siguientes comandos:";
		$output.="<pre>";
			foreach($instances as $instance):
				$output.="sapcontrol -nr ".sprintf("%02d", $instance['inst_no'])." -function StartService ".$system['sid']."<br/>";
			endforeach;
		$output.="</pre>";		
	$output.="</blockquote>";
	//---------------------------------------------------------------------------
	$output.='<h2>Inicio de Base de Datos</h2>';
	$output.="<blockquote>";
		$output.="Para subir la Base de Datos de forma Manual (al subir SAP la Base de Datos debe subir de modo autom&aacute;tico) debe ser ejecutado con el usuario <em>db2".strtolower($system['sid'])."</em>):";
		$output.="<pre>db2start</pre>";
	$output.="</blockquote>";
	//---------------------------------------------------------------------------
	$output.='<h2>Inicio de Aplicaci&oacute;n SAP</h2>';
	$output.="<blockquote>";
		$output.="Con el usuario <em>".strtolower($system['sid'])."adm</em> se deben ejecutar los comandos en el siguiente orden:";
		$output.="<pre>";
			foreach($instances as $instance):
				$output.="startsap ".$instance['hostname']."<br/>";
			endforeach;
		$output.="</pre>";
	$output.="</blockquote>";
	
	
	
	$output.="<hr/>";
	return $output;
}
































?>
