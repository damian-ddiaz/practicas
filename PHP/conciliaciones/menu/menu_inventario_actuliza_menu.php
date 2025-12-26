<?php
sc_reset_menu_delete();
sc_reset_menu_disable();

if([desactivar_btn_volver] === 'SI'){
	sc_menu_delete(item_41);	
}

buscardatoslogin([usr_login]);
// Administracion Inicio
// Adminidtrativo - Contabilidad - Finanzas - Recursos Humanos
	if([permite_url_portal_administrativo] === 'NO'){
		sc_menu_disable(item_718);
	}
	if([permite_url_portal_contabilidad] === 'NO'){
		sc_menu_disable(item_719);
	}
	if([permite_url_portal_finanzas] === 'NO'){
		sc_menu_disable(item_720;
	}
	if([permite_url_portal_rrhh] === 'NO'){
		sc_menu_disable(item_721);
	}
// Administracion Fin

// Operaciones Inicio
// Dispositivos - Inventario - Mantenimiento - Pos - Tareas y Proyectos
	if([permite_url_portal_dispositos] === 'NO'){
		sc_menu_disable(item_722);
	}
	if([permite_url_portal_inventario] === 'NO'){
		sc_menu_disable(item_723);
	}
	if([permite_url_portal_mantenimiento] === 'NO'){
		sc_menu_disable(item_724);
	}
	if([permite_url_portal_puntodeventa] === 'NO'){
		sc_menu_disable(item_725;
	}
	if([permite_url_portal_proyectos] === 'NO'){
		sc_menu_disable(item_726);
	}
// Operaciones Fin

// Comercial Inicio
// Aliados - Atencion Comercial- Eventos - Patrocinantes - Vendedor
	if([permite_url_portal_aliado] === 'NO'){
		sc_menu_disable(item_727);
	}
	if([permite_url_portal_atencion_comercial] === 'NO'){
		sc_menu_disable(item_728);
	}
	if([permite_url_portal_eventos] === 'NO'){
		sc_menu_disable(item_729);
	}
	if([permite_url_portal_patrocinadores] === 'NO'){
		sc_menu_disable(item_730;
	}

	if([permite_url_portal_vendedor] === 'NO'){
		sc_menu_disable(item_731;
	}
// Comercial Fin

// Soporte y Herramientas - Inicio
// Gestion de Redes - Helpdesk - Herramientas - Soporte Tecnico
	if([permite_url_portal_gestionredes] === 'NO'){
		sc_menu_disable(item_732);
	}
	if([permite_url_portal_helpdesk] === 'NO'){
		sc_menu_disable(item_733);
	}
	if([permite_url_portal_herramientas] === 'NO'){
		sc_menu_disable(item_734);
	}
	if([permite_url_portal_soporte] === 'NO'){
		sc_menu_disable(item_735);
	}
// Soporte y Herramientas Fin

// Reportes - Inicio
// Reportes Empresa - Reportes Sucursal
	if([permite_url_portal_reportes_matriz] === 'NO'){
		sc_menu_disable(item_736);
	}
	if([permite_url_portal_reportes] === 'NO'){
		sc_menu_disable(item_737);
	}
// Reportes Fin

// Otros - Inicio
// Configucaion - Intranet
	if([permite_url_portal_configuracion] === 'NO'){
		sc_menu_disable(item_738);
	}
	if([permite_url_portal_intranet] === 'NO'){
		sc_menu_disable(item_739);
	}
// Otros Fin





?>