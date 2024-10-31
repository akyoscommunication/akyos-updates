<?php

namespace AkyosUpdates\Service\WPMU;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Hummingbird\Core\Utils;

class HummingbirdOptionsService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_hummingbird_options';
	}

	/**
	 * @throws \JsonException
	 */
	public function getSettings()
	{
		$module = Utils::get_module('page_cache');
		$options = $module->get_options();
		$existing_settings = $module->get_settings();
		$message = '';

		if ($options['enabled']) {
			$message .= '<p>✅ Page Caching activé</p>';
		} else {
			$message .= '<p>⭕ Page Caching désactivé</p>';
		}

		$message .= '<ul>';

		if ($options['preload']) {
			$message .= '<li><p>✅ Page Caching : Préchargement activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Préchargement désactivé</p></li>';
		}

		if ($options['integrations']['varnish']) {
			$message .= '<li><p>✅ Page Caching : Varnish activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Varnish désactivé</p></li>';
		}


		if ($existing_settings['clear_interval']['enabled']) {
			$message .= '<li><p>✅ Page Caching : Clear Interval activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Clear Interval désactivé</p></li>';
		}

		if ($existing_settings['settings']['clear_update']) {
			$message .= '<li><p>✅ Page Caching : Clear full cache when post/page is updated activé</p></li>';
		} else {
			$message .= '<li><p>⭕ Page Caching : Clear full cache when post/page is updated désactivé</p></li>';
		}

		$message .= '</ul>';

		$message .= '<ul>';
		$message .= '<li>🔧 Si ce n\'est pas déjà activé, lancer la configuration => sélectionner "Apache", laisser "All files types" et "1 year", valider et tout se fait tout seul. S\'il y a un souci il vous proposera de copier-coller du code dans le .htaccess puis de revalider.<a href="'.admin_url('admin.php?page=wphb-caching&view=caching').'">Cliquez ICI</a></li>';
		$message .= '<li>🔧 Onglet Caching/Intégration (à faire en prod) : Si le site est sur Cloudflare (à vérifier pour les anciens sites, à faire tout le temps pour les nouveaux), mettre en place l\'intégration Cloudflare.<a href="'.admin_url('admin.php?page=wphb-caching&view=integrations').'">Cliquez ICI</a></li>';
		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => true
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_set_settings')]
	public function setSettings()
	{
		$module = Utils::get_module('page_cache');
		$options = $module->get_options();
		$existing_settings = $module->get_settings();

		$options['enabled'] = true;
		$options['preload'] = true;
		$options['integrations']['varnish'] = '1';
		$existing_settings['clear_interval']['enabled'] = true;
		$existing_settings['settings']['clear_update'] = 1;

		$module->update_options($options);
		$module->save_settings($existing_settings);

		set_transient('akyos_success_notice', 'Mise à jour des paramètres de Humingbird réalisée avec succès !', 30);

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}


	public function getGzip()
	{
		$message = '<ul>';
		$message .= '<li>🔧 Onglet GzipCompression: si ce n\'est pas fait, sélectionner "Apache", "Automatique" et lancer la configuration. Dans 90% des cas ça fonctionne, si ce n\'est pas le cas, copier-coller le code fourni dans le .htaccess. <a href="'.admin_url('admin.php?page=wphb-gzip').'">Cliquez ICI</a></li>';
		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => false
		];
	}

	public function getAssetsOptimization()
	{
		$message = '<ul>';
		$message .= '<li>🔧 Onglet Assets Optimization: en général je n\'y touche pas, il est arrivé qu\'il y ait des soucis avec des plugins et qu\'on ait du se servir de cette fonctionnalité mais ce n\'est pas obligatoire. <a href="'.admin_url('admin.php?page=wphb-minification').'">Cliquez ICI</a></li>';
		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => false
		];
	}

	public function getAdvancedTools()
	{
		$message = '<ul>';
		$message .= '<li>🔧 Onglet Advanced Tools / General : cocher les options "Remove query strings from my assets" et "Remove the default Emoji JS & CSS files", si WooCommerce est installé alors cocher également "Disable cart fragments" <a href="'.admin_url('admin.php?page=wphb-advanced').'">Cliquez ICI</a></li>';
		$message .= '<li>🔧 Onglet Advanced Tools / Database cleanup (MEL / Maintenance): lors de la mise en ligne ou d\'une maintenance, lancer la suppression de tous les éléments de la liste SAUF DES BROUILLONS car les clients mettent souvent des articles/pages en brouillon avant de les publier" <a href="'.admin_url('admin.php?page=wphb-advanced&view=db').'">Cliquez ICI</a></li>';
		$message .= '</ul>';

		return [
			'message' => $message,
			'action_required' => false
		];
	}
}
