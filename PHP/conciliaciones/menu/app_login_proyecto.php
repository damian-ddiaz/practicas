<?php

	$sql = "SELECT 
		app_name,
		priv_access,
		priv_insert,
		priv_delete,
		priv_update,
		priv_export,
		priv_print
	      FROM seguridad_groups_apps
	      WHERE group_id IN
	          (SELECT
		       group_id
		   FROM
		       seguridad_users_groups 
		   WHERE
		       login = '". [usr_login] ."')";
		
	
sc_select(rs, $sql);

$arr_default = array(
					'access' => 'off',
					'insert' => 'off',
					'delete' => 'off',
					'update' => 'off',
					'export' => 'btn_display_off',
					'print'  => 'btn_display_off',
					);
if ({rs} !== false)
{
	$arr_perm = array();
	while (!$rs->EOF)
	{
		$app = $rs->fields[0];
		
		if(!isset($arr_perm[$app]))
		{
		   $arr_perm[$app] = $arr_default;
		}
		if( $rs->fields[1] == 'Y')
		{
			$arr_perm[$app][ 'access' ] = 'on';
		}
		if($rs->fields[2] == 'Y')
		{
			$arr_perm[$app][ 'insert' ] = 'on';
		}
		if($rs->fields[3] == 'Y')
		{
			$arr_perm[$app][ 'delete' ] = 'on';
		}
		if($rs->fields[4] == 'Y')
		{
			$arr_perm[$app][ 'update' ] = 'on';
		}
		if($rs->fields[5] == 'Y')
		{
			$arr_perm[$app]['export'] =  'btn_display_on';
		}
		if($rs->fields[6] == 'Y')
		{
			$arr_perm[$app]['print'] =  'btn_display_on';
		}


		$rs->MoveNext();	
	}
	$rs->Close();
		   
	foreach($arr_perm as $app => $perm)
	{
		sc_apl_status($app, $perm['access']);
		
		sc_apl_conf($app, 'insert', $perm['insert']);
		sc_apl_conf($app, 'delete', $perm['delete']);
		sc_apl_conf($app, 'update', $perm['update']);
		sc_apl_conf($app, $perm['export'], 'xls');
		sc_apl_conf($app, $perm['export'], 'word');
		sc_apl_conf($app, $perm['export'], 'pdf');
		sc_apl_conf($app, $perm['export'], 'xml');
		sc_apl_conf($app, $perm['export'], 'csv');
		sc_apl_conf($app, $perm['export'], 'rtf');
		sc_apl_conf($app, $perm['export'], 'json');
		sc_apl_conf($app, $perm['print'], 'print');

	}
		
		
	
		sc_log_add('login', {lang_login_ok});
		
        if([sett_session_expire] != 'N'){
         //   sc_apl_default('app_Login_reportes', [sett_session_expire]);
        }
	
	
}

$desactivar_btn_volver = 'SI';
sc_set_global($desactivar_btn_volver);
		

	
		
      //  sc_apl_default('app_Login_inventario', 'R');
		sc_redir('menu_configuracion');	
	
?>