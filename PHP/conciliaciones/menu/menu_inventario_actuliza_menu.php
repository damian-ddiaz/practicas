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
		sc_menu_disable(item_45);
	}
	if([permite_url_portal_contabilidad] === 'NO'){
		sc_menu_disable(item_46);
	}
	if([permite_url_portal_finanzas] === 'NO'){
		sc_menu_disable(item_47);
	}
	if([permite_url_portal_rrhh] === 'NO'){
		sc_menu_disable(item_48);
	}
// Administracion Fin

// Operaciones Inicio
// Dispositivos - Inventario - Mantenimiento - Pos - Tareas y Proyectos
	if([permite_url_portal_dispositos] === 'NO'){
		sc_menu_disable(item_51);
	}
	if([permite_url_portal_inventario] === 'NO'){
		sc_menu_disable(item_52);
	}
	if([permite_url_portal_mantenimiento] === 'NO'){
		sc_menu_disable(item_53);
	}
	if([permite_url_portal_puntodeventa] === 'NO'){
		sc_menu_disable(item_54);
	}
	if([permite_url_portal_proyectos] === 'NO'){
		sc_menu_disable(item_50);
	}
// Operaciones Fin

// Comercial Inicio
// Aliados - Atencion Comercial- Eventos - Patrocinantes - Vendedor
	if([permite_url_portal_aliado] === 'NO'){
		sc_menu_disable(item_56);
	}
	if([permite_url_portal_atencion_comercial] === 'NO'){
		sc_menu_disable(item_57);
	}
	if([permite_url_portal_eventos] === 'NO'){
		sc_menu_disable(item_58);
	}
	if([permite_url_portal_patrocinadores] === 'NO'){
		sc_menu_disable(item_59);
	}

	if([permite_url_portal_vendedor] === 'NO'){
		sc_menu_disable(item_60);
	}
// Comercial Fin

// Soporte y Herramientas - Inicio
// Gestion de Redes - Helpdesk - Herramientas - Soporte Tecnico
	if([permite_url_portal_gestionredes] === 'NO'){
		sc_menu_disable(item_62);
	}
	if([permite_url_portal_helpdesk] === 'NO'){
		sc_menu_disable(item_63);
	}
	if([permite_url_portal_herramientas] === 'NO'){
		sc_menu_disable(item_64);
	}
	if([permite_url_portal_soporte] === 'NO'){
		sc_menu_disable(item_65);
	}
// Soporte y Herramientas Fin

// Reportes - Inicio
// Reportes Empresa - Reportes Sucursal
	if([permite_url_portal_reportes_matriz] === 'NO'){
		sc_menu_disable(item_67);
	}
	if([permite_url_portal_reportes] === 'NO'){
		sc_menu_disable(item_68);
	}
// Reportes Fin

// Otros - Inicio
// Configucaion - Intranet
	if([permite_url_portal_configuracion] === 'NO'){
		sc_menu_disable(item_70);
	}
	if([permite_url_portal_intranet] === 'NO'){
		sc_menu_disable(item_71);
	}
// Otros Fin





?>