<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Adds an explain text to the postlove_hide custom profile field.
 *
 * release_1_2_0_create_cpf created the field through phpBB's generic
 * profilefield_base_migration::create_custom_field(), which always inserts
 * lang_explain as an empty string. The field shipped with a label only
 * ("Hide Like icons and summaries") and no indication of what checking it
 * actually does, which is how a maintainer's own account ended up with likes
 * hidden board-wide without realising it (#24).
 */
class release_2_2_7_add_hide_explain extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_5_remove_dead_config',
		];
	}

	/**
	 * One sentence per installed board language (iso => text). A board
	 * without a given language installed simply matches zero rows in
	 * set_explain_text(), so this list does not need to be exhaustive.
	 */
	private const EXPLAIN_BY_ISO = [
		'en'	=> 'Hides the like button on every post, and removes the most-liked-posts panels from the board index and forums. This only affects what you see — other members can still see and use likes normally.',
		'bg'	=> 'Скрива бутона за харесване на всяка публикация и премахва панелите с най-харесваните публикации от началната страница и форумите. Това засяга само вашия собствен изглед — останалите потребители продължават да виждат и използват харесванията нормално.',
		'cs'	=> 'Skryje tlačítko Líbí se u každého příspěvku a odstraní panely nejoblíbenějších příspěvků z hlavní stránky a fór. Týká se to pouze vašeho vlastního zobrazení — ostatní uživatelé mohou lajky nadále běžně vidět a používat.',
		'de'	=> 'Blendet die Gefällt-mir-Schaltfläche bei jedem Beitrag aus und entfernt die Übersichten der beliebtesten Beiträge von der Forenübersicht und den Foren. Dies betrifft nur deine eigene Ansicht — andere Mitglieder können Gefällt-mir weiterhin normal sehen und nutzen.',
		'es'	=> 'Oculta el botón Me gusta en cada mensaje y elimina los paneles de mensajes más gustados del índice y los foros. Esto solo afecta a tu propia vista: los demás miembros pueden seguir viendo y usando los Me gusta con normalidad.',
		'fr'	=> 'Masque le bouton J\'aime sur chaque message et retire les panneaux des messages les plus aimés de l\'index et des forums. Cela ne concerne que votre propre affichage : les autres membres peuvent toujours voir et utiliser les J\'aime normalement.',
		'nl'	=> 'Verbergt de vind-ik-leuk-knop op elk bericht en verwijdert de panelen met meest gewaardeerde berichten van de forumindex en de forums. Dit heeft alleen invloed op jouw eigen weergave — andere leden kunnen vind-ik-leuk nog gewoon zien en gebruiken.',
		'pl'	=> 'Ukrywa przycisk polubienia przy każdym poście i usuwa panele najbardziej polubionych postów ze strony głównej i forów. Dotyczy to tylko Twojego widoku — inni użytkownicy nadal widzą i mogą korzystać z polubień normalnie.',
		'pt_br'	=> 'Oculta o botão de curtir em cada mensagem e remove os painéis de mensagens mais curtidas do índice e dos fóruns. Isso afeta apenas a sua própria visualização — os outros membros continuam vendo e usando as curtidas normalmente.',
		'tr'	=> 'Her gönderideki beğeni düğmesini gizler ve en çok beğenilen gönderiler panellerini forum ana sayfasından ve forumlardan kaldırır. Bu yalnızca sizin görünümünüzü etkiler — diğer üyeler beğenileri normal şekilde görmeye ve kullanmaya devam edebilir.',
	];

	public function effectively_installed()
	{
		$sql = "SELECT pl.lang_explain
			FROM " . PROFILE_LANG_TABLE . " pl
			JOIN " . PROFILE_FIELDS_TABLE . " pf ON pf.field_id = pl.field_id
			WHERE pf.field_name = 'postlove_hide'
				AND pl.lang_explain <> ''";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (bool) $row;
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'set_explain_text']]],
		];
	}

	public function set_explain_text()
	{
		$sql = "SELECT field_id FROM " . PROFILE_FIELDS_TABLE . " WHERE field_name = 'postlove_hide'";
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);

		if (!$field_id)
		{
			return;
		}

		foreach (self::EXPLAIN_BY_ISO as $iso => $explain)
		{
			$sql = "SELECT lang_id FROM " . LANG_TABLE . "
				WHERE lang_iso = '" . $this->db->sql_escape($iso) . "'";
			$result = $this->db->sql_query($sql);
			$lang_id = (int) $this->db->sql_fetchfield('lang_id');
			$this->db->sql_freeresult($result);

			if (!$lang_id)
			{
				continue;
			}

			$sql = 'UPDATE ' . PROFILE_LANG_TABLE . "
				SET lang_explain = '" . $this->db->sql_escape($explain) . "'
				WHERE field_id = " . $field_id . '
					AND lang_id = ' . $lang_id;
			$this->db->sql_query($sql);
		}
	}

	// No revert_data(): the pre-migration value is always '', and leaving the
	// explain text behind on downgrade is harmless.
}
