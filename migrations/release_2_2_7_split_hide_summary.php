<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\migrations;

/**
 * Splits the single postlove_hide opt-out into two independent profile
 * fields. postlove_hide keeps its original meaning (like button + the
 * user's own "Likes" link on their profile); this migration adds
 * postlove_hide_sum (field_ident is capped at 20 chars, hence the
 * abbreviation) for the most-liked-posts summary panels and the viewforum
 * heart count. Before this, a user who wanted to keep liking posts but had
 * no interest in the summary panels had no way to drop one without the
 * other (#55).
 *
 * Also corrects postlove_hide's explain text, set in
 * release_2_2_7_add_hide_explain, which still described the now-narrower
 * button-only scope as covering summaries too.
 */
class release_2_2_7_split_hide_summary extends \phpbb\db\migration\profilefield_base_migration
{
	public static function depends_on()
	{
		return [
			'\avathar\postlove\migrations\release_2_2_7_add_hide_explain',
		];
	}

	protected $profilefield_name = 'postlove_hide_sum';
	protected $profilefield_database_type = ['UINT:2', 0];
	protected $profilefield_data = [
		'field_name'            => 'postlove_hide_sum',
		'field_type'			=> 'profilefields.type.bool',
		'field_ident'           => 'postlove_hide_sum',
		'field_length'          => '2',
		'field_default_value'	=> 0,
		'field_required'		=> 0,
		'field_show_novalue'	=> 0,
		'field_show_on_reg'		=> 0,
		'field_show_on_pm'		=> 0,
		'field_show_on_vt'		=> 0,
		'field_show_profile'	=> 1,
		'field_show_on_ml'		=> 0,
		'field_hide'			=> 0,
		'field_no_view'			=> 1,
		'field_active'			=> 1,
		'field_is_contact'		=> 0,
		'field_contact_desc'	=> '',
		'field_contact_url'		=> '',
	];

	/**
	 * One sentence per installed board language (iso => text), for both
	 * postlove_hide (narrowed wording) and the new postlove_hide_sum.
	 * A board without a given language installed simply matches zero rows
	 * in set_explain_text(), so this list does not need to be exhaustive.
	 */
	private const EXPLAIN_BY_ISO = [
		'postlove_hide'	=> [
			'en'	=> 'Hides the like button on every post, and your own "Likes" link on your profile. Most-liked-posts summaries are controlled separately, below.',
			'bg'	=> 'Скрива бутона за харесване на всяка публикация и вашия собствен линк „Харесвания“ в профила. Панелите с най-харесваните публикации се управляват отделно по-долу.',
			'cs'	=> 'Skryje tlačítko Líbí se u každého příspěvku a váš vlastní odkaz „Oblíbené“ v profilu. Panely nejoblíbenějších příspěvků se nastavují samostatně níže.',
			'de'	=> 'Blendet die Gefällt-mir-Schaltfläche bei jedem Beitrag sowie deinen eigenen „Gefällt mir“-Link im Profil aus. Die Übersichten der beliebtesten Beiträge werden weiter unten separat gesteuert.',
			'es'	=> 'Oculta el botón Me gusta en cada mensaje y tu propio enlace "Me gusta" en tu perfil. Los paneles de mensajes más gustados se controlan por separado más abajo.',
			'fr'	=> 'Masque le bouton J\'aime sur chaque message, ainsi que votre propre lien « J\'aime » sur votre profil. Les panneaux des messages les plus aimés sont contrôlés séparément ci-dessous.',
			'nl'	=> 'Verbergt de vind-ik-leuk-knop op elk bericht en je eigen "vind-ik-leuks"-link op je profiel. De panelen met meest gewaardeerde berichten worden hieronder apart ingesteld.',
			'pl'	=> 'Ukrywa przycisk polubienia przy każdym poście oraz Twój własny link „Polubienia” w profilu. Panele najbardziej polubionych postów są ustawiane osobno poniżej.',
			'pt_br'	=> 'Oculta o botão de curtir em cada mensagem e o seu próprio link de "Curtidas" no perfil. Os painéis de mensagens mais curtidas são controlados separadamente abaixo.',
			'tr'	=> 'Her gönderideki beğeni düğmesini ve profilinizdeki kendi "Beğeniler" bağlantınızı gizler. En çok beğenilen gönderiler panelleri aşağıda ayrı olarak ayarlanır.',
		],
		'postlove_hide_sum'	=> [
			'en'	=> 'Hides the most-liked-posts panels on the board index and forums, and the like count on the topic list. Does not affect the like button on individual posts.',
			'bg'	=> 'Скрива панелите с най-харесваните публикации от началната страница и форумите, както и броя на харесванията в списъка с теми. Не засяга бутона за харесване на отделните публикации.',
			'cs'	=> 'Skryje panely nejoblíbenějších příspěvků na hlavní stránce a fórech a počet lajků v seznamu témat. Neovlivňuje tlačítko Líbí se u jednotlivých příspěvků.',
			'de'	=> 'Blendet die Übersichten der beliebtesten Beiträge auf der Forenübersicht und in den Foren sowie die Like-Anzahl in der Themenliste aus. Die Gefällt-mir-Schaltfläche bei einzelnen Beiträgen ist davon nicht betroffen.',
			'es'	=> 'Oculta los paneles de mensajes más gustados del índice y los foros, y el contador de Me gusta en la lista de temas. No afecta al botón Me gusta de cada mensaje.',
			'fr'	=> 'Masque les panneaux des messages les plus aimés sur l\'index et les forums, ainsi que le nombre de J\'aime dans la liste des sujets. N\'affecte pas le bouton J\'aime sur chaque message.',
			'nl'	=> 'Verbergt de panelen met meest gewaardeerde berichten op de forumindex en de forums, en het aantal vind-ik-leuks in de onderwerpenlijst. Heeft geen invloed op de vind-ik-leuk-knop bij afzonderlijke berichten.',
			'pl'	=> 'Ukrywa panele najbardziej polubionych postów na stronie głównej i forach oraz licznik polubień na liście tematów. Nie wpływa na przycisk polubienia przy pojedynczych postach.',
			'pt_br'	=> 'Oculta os painéis de mensagens mais curtidas do índice e dos fóruns, e o contador de curtidas na lista de tópicos. Não afeta o botão de curtir em mensagens individuais.',
			'tr'	=> 'En çok beğenilen gönderiler panellerini forum ana sayfasından ve forumlardan, ayrıca konu listesindeki beğeni sayısını gizler. Tek tek gönderilerdeki beğeni düğmesini etkilemez.',
		],
	];

	public function update_data()
	{
		return [
			['custom', [[$this, 'create_custom_field']]],
			['custom', [[$this, 'set_explain_text']]],
		];
	}

	public function set_explain_text()
	{
		foreach (self::EXPLAIN_BY_ISO as $field_name => $explain_by_iso)
		{
			$sql = "SELECT field_id FROM " . PROFILE_FIELDS_TABLE . "
				WHERE field_name = '" . $this->db->sql_escape($field_name) . "'";
			$result = $this->db->sql_query($sql);
			$field_id = (int) $this->db->sql_fetchfield('field_id');
			$this->db->sql_freeresult($result);

			if (!$field_id)
			{
				continue;
			}

			foreach ($explain_by_iso as $iso => $explain)
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
	}

	// No revert_data(): reverting only drops the postlove_hide_sum schema
	// column via revert_schema(); postlove_hide's explain text is left as
	// this migration set it, which is harmless on downgrade.
}
