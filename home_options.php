<?php
/**
 * Fichier options
 *
 * @plugin     Home
 * @copyright  2014
 * @author     Cédric
 * @licence    GNU/GPL
 * @package    SPIP\Home\options
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

if (_request('var_mode')=='home'){
	define('_VAR_MODE','calcul');
	define('_VAR_NOCACHE',true);
}

/**
 * Tester si la home est editable pour afficher les boutons
 * @param bool $item
 *   test si pour les boutons d'un item (true) ou le bouton toggle edition general (false)
 * @return bool
 */
function home_editable($item=false){
	static $editable=null;
	if ($item AND _request('var_mode')!=='home')
		return false;
	if (is_null($editable)){
		$editable = false;
		if (isset($GLOBALS['visiteur_session']['statut'])
			AND $GLOBALS['visiteur_session']['statut']=='0minirezo'
			AND include_spip('inc/autoriser')
			AND autoriser('administrer', 'home')) {
			$editable = true;
		}
	}
	return $editable;
}

/**
 * La balise renvoie le nombre d'items affiches dans la home
 * @param $p
 * @return mixed
 */
function balise_HOME_NB_ITEMS_dist($p) {
	$p->code = "home_nb_items()";
	$p->interdire_scripts = false;
	return $p;
}

/**
 * Afficher les boutons d'admin generaux de la home
 * @param $p
 * @return mixed
 */
function balise_BOUTONS_ADMIN_HOME_dist($p) {
	$p->code = "'<style>'.spip_file_get_contents(find_in_path('css/home-grids.css')).'</style>'";

	// les boutons d'admin en supplement

	$p->code .= "
. '<'.'?php
	if (function_exists(\'home_editable\') AND home_editable()) {
		echo \"<div class=\'boutons spip-admin actions administrerhome\'>\" . home_boutons_admin() . \"</div>\";
	}
?'.'>'";

	$p->interdire_scripts = false;
	return $p;
}

/**
 * Afficher les boutons d'admin d'un item de la home
 *
 * @param $p
 * @return mixed
 */
function balise_BOUTONS_ADMIN_HOME_ITEM_dist($p) {

	$compteur_boucle = charger_fonction("COMPTEUR_BOUCLE","balise");
	$p = $compteur_boucle($p);
	$_compteur = $p->code;

	// les boutons d'admin de l'item en supplement
	$p->code = "
'<'.'?php
	if (function_exists(\'home_editable\') AND home_editable(true)) {
			echo home_boutons_admin_item('.$_compteur.');
		}
?'.'>'";

	$p->interdire_scripts = false;
	return $p;
}

/**
 * Afficher les boutons d'admin de la home
 *
 * @return string
 */
function home_boutons_admin(){
	$edit = (_request('var_mode')=='home'?true:false);
	if ($edit){
		include_spip('inc/autoriser');
		$edit = autoriser('administrer','home');
	}

	$bouton_action = charger_filtre("bouton_action");

	$boutons = "";
	if ($edit){
		$redirect = parametre_url(self(),'var_mode','home');
		$boutons .= $bouton_action(
			_T('home:bouton_home_reinit'),
			generer_action_auteur('instituer_home',"raz//",$redirect),'btn-mini pull-left btn-danger',_T('home:label_confirm_reinit'));
		$boutons .= $bouton_action(
			_T('home:bouton_home_rowadd'),
			generer_action_auteur('instituer_home',"rowadd//",$redirect),'btn-small','',_T('home:bouton_home_rowadd_title'));
		$boutons .= $bouton_action(
			_T('home:bouton_home_rowdel'),
			generer_action_auteur('instituer_home',"rowdel//",$redirect),'btn-small','',_T('home:bouton_home_rowdel_title'));
	}

	$boutons .= $bouton_action(
		$edit?_T('home:bouton_home_fin_editer'):_T('home:bouton_home_editer'),
		$edit?self():parametre_url(self(),'var_mode','home'),'btn-small ');

	return $boutons;
}

/**
 * Afficher les boutons d'admin d'un item de la home
 *
 * @param int $no
 *   numero de l'article, compte a partir de 1 (#COMPTEUR_BOUCLE)
 * @return string
 */
function home_boutons_admin_item($no){
	$edit = (_request('var_mode')=='home'?true:false);
	if ($edit){
		include_spip('inc/autoriser');
		$edit = autoriser('administrer','home');
	}
	if (!$edit) return "";
	$items = home_items();
	if (!isset($items[$no-1]))
		return "";

	$item = $items[$no-1];
	$no_ligne = $item['row'];
	$no_slot = $item['slot'];

	$bouton_action = charger_filtre("bouton_action");

	$boutons = "";
	$redirect = ancre_url(parametre_url(self(),'var_mode','home'),"bahi-$no");

	if ($no_slot==0){
		$boutons .= $bouton_action(
			_T('home:bouton_home_slotdel'),
			generer_action_auteur('instituer_home', "slotdel/$no_ligne/", $redirect), 'btn-small', '', _T('home:bouton_home_slotdel_title'));
		$boutons .= $bouton_action(
			_T('home:bouton_home_slotadd'),
			generer_action_auteur('instituer_home', "slotadd/$no_ligne/", $redirect), 'btn-small', '', _T('home:bouton_home_slotadd_title'));
	}

	if ($item['width']>1 OR $item['of']==2){
		$boutons .= $bouton_action(
			_T('home:bouton_home_slotshrink'),
			generer_action_auteur('instituer_home',"slotshrink/$no_ligne/$no_slot",$redirect),'btn-small','',_T('home:bouton_home_slotshrink_title'));
	}
	// pas de enlarge sur le dernier, car il prend la place qui lui reste
	if (strpos($item['class'],'lastUnit')===false){
		if ($item['width']<$item['of']-1 OR $item['of']==2){
			$boutons .= $bouton_action(
				_T('home:bouton_home_slotenlarge'),
				generer_action_auteur('instituer_home',"slotenlarge/$no_ligne/$no_slot",$redirect),'btn-small','',_T('home:bouton_home_slotenlarge_title'));
		}
	}

	$boutons = "<div class='boutons spip-admin actions administrerhomeitem' id='bahi-$no'>"
		//.$item['width']."/".$item['of'] // debug
		.$boutons.
		"</div>";
	return $boutons;
}

/**
 * Recuperer la config de la home, et fournir une config par defaut si besoin
 * @return array
 */
function home_config(){
	include_spip("inc/config");
	$config = array(
		'nb_lignes' => lire_config("home/nb_lignes",5),
		'lignes' => lire_config("home/lignes",array()),
	);

	return $config;
}

/**
 * Recuperer une ligne de la home par son numero, et la renseigner par defaut si pas connue
 * @param int $no
 *   numero de la ligne, comptee a partir de 0 (zero)
 * @return array
 */
function home_ligne($no){
	$config = home_config();
	$ligne = array("nb_slots"=>$no?2:1,"items"=>array());
	if (isset($config['lignes'][$no]))
		$ligne = $config['lignes'][$no];
	$ligne['no'] = $no;
	return $ligne;
}

/**
 * Decrire chaque item de la ligne a partir de sa description simplifiee
 * @param array $ligne
 *   int nb_slots : nombre de slots dans la ligne
 *   array items : description de chaque item
 *     int width : largeur en slots de chaque item
 * @return array
 */
function home_ligne_items($ligne){
	$syn = array("size2of4"=>"size1of2");

	$items = array();
	$nb_slots = (isset($ligne["nb_slots"])?$ligne["nb_slots"]:1);
	$free_slots = $nb_slots;
	for($i=0;$i<$nb_slots;$i++){
		$item = array();
		if (is_array($ligne['items']) AND count($ligne['items']))
			$item = array_shift($ligne['items']);
		$width = isset($item['width'])?$item['width']:1;
		$width = min($free_slots,$width);
		$free_slots -= $width;
		if ($width==$nb_slots){
			$width = $nb_slots = 1;
		}

		$class = "size{$width}of{$nb_slots}";
		if (isset($syn[$class])) $class = $syn[$class];
		$class = "unit of{$nb_slots} $class";
		if ($i==0) $class .= " firstUnit";
		if ($free_slots<=0) $class .= " lastUnit";
		if (!$ligne['no'])
			$class .= " firstRow";
		$items[] = array(
			'row' => $ligne['no'],
			'slot' => $i,
			'width' => $width,
			'of' => $nb_slots,
			'class' => trim($class),
		);

		if ($free_slots<=0)
			break;
	}
	if ($items[0]['of']==4
	  AND count($items)==2
	  AND $items[0]['width']==2
	  AND $items[1]['width']==2){
		$items[0]['of'] = $items[1]['of'] = 2;
		$items[0]['width'] = $items[1]['width'] = 1;
	}
	return $items;
}

/**
 * Construire tout le tableau qui decrit chaque item de la home
 *
 * @param bool $reload
 * @return array
 */
function home_items($reload=false){
	static $items;
	if (is_null($items) OR $reload){
		$config = home_config();
		$items = array();
		for($l=0;$l<$config['nb_lignes'];$l++){
			$litems = home_ligne_items(home_ligne($l));
			$items = array_merge($items,$litems);
		}
	}
	return $items;
}

/**
 * Compter le nombre d'items dans la home
 * @return int
 */
function home_nb_items(){
	return count(home_items());
}

/**
 * Retourner la classe de chaque item
 * @param int $no
 *   numero de l'item, compte a partir de 1 (#COMPTEUR_BOUCLE)
 * @return string
 */
function home_class_item($no){
	$items = home_items();
	if (isset($items[$no-1]['class']))
		return $items[$no-1]['class'];

	// si on a depasse le quota car pas utilise le compteur, on renvoie des lignes de 1 item
	return "unit of1 size1of1 firstUnit lastUnit";
}