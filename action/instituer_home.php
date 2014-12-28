<?php
/**
 * Fichier action
 *
 * @plugin     Home
 * @copyright  2014
 * @author     Cédric
 * @licence    GNU/GPL
 * @package    SPIP\Home\action
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


function action_instituer_home_dist($arg=null) {

	if (!function_exists('lire_config'))
		include_spip('inc/config');

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	list($action,$no_ligne,$no_slot) = explode('/', $arg);

	if (autoriser('administrer','home')){

		$config = home_config();

		switch($action){
			case "raz":
				effacer_config("home");
				$config = array();
				break;
			case "rowadd":
				$config["nb_lignes"]++;
				$config["lignes"][$config["nb_lignes"]-1] = array("nb_slots"=>2);
				break;
			case "rowdel":
				$config["nb_lignes"]--;
				break;
			case "slotadd":
				$ligne = home_ligne($no_ligne);
				$ligne['nb_slots']++;
				$ligne['nb_slots'] = min($ligne['nb_slots'],5);
				$config['lignes'][$no_ligne] = $ligne;
				break;
			case "slotdel":
				$ligne = home_ligne($no_ligne);
				$ligne['nb_slots']--;
				$ligne['nb_slots'] = max($ligne['nb_slots'],1);
				$config['lignes'][$no_ligne] = $ligne;
				break;
			case "slotenlarge":
			case "slotshrink":
				$coeff = 1;
				$ligne = home_ligne($no_ligne);
				if ($ligne['nb_slots']==2){
					$coeff = 2;
					$ligne['nb_slots'] = 4;
				}
				$ligne_items = home_ligne_items($ligne);
				$ligne['items'] = array();
				foreach($ligne_items as $li){
					$ligne['items'][] = array('width'=>$li['width'] * $coeff);
				}
				if (isset($ligne['items'][$no_slot])){
					if ($action=="slotenlarge")
						$ligne['items'][$no_slot]['width']++;
					else
						$ligne['items'][$no_slot]['width']--;
				}
				$config['lignes'][$no_ligne] = $ligne;
				break;
		}

		ecrire_config("home/",$config);
		$lignes = array();
		for($l=0;$l<$config['nb_lignes'];$l++){
			$lignes[$l] = array();
			$litems = home_ligne_items(home_ligne($l));
			if (count($litems))
				$lignes[$l]['nb_slots'] = $litems[0]['of'];
			else {
				$lignes[$l]['nb_slots'] = 1;
			}
			$lignes[$l]['items'] = array();
			foreach($litems as $li){
				$lignes[$l]['items'][] = array('width'=>$li['width']);
			}
		}
		$config['lignes'] = $lignes;
		ecrire_config("home/",$config);

		// invalider le cache
		include_spip('inc/invalideur');
		suivre_invalideur("id='home'");
	}
}