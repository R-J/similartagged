﻿<?php
$PluginInfo['similarTagged'] = [
    'Name' => 'Similar Tagged',
    'Description' => 'Adds a "Similar Tagged" module to discussions.',
    'Version' => '0.1.0',
    'RequiredApplications' => ['Vanilla' => '>= 2.3'],
    'RequiredPlugins' => ['Tagging' => '>=1.8.12'],
    'SettingsPermission' => 'Garden.Settings.Manage',
    'SettingsUrl' => '/dashboard/settings/similartagged',
    'MobileFriendly' => true,
    'HasLocale' => true,
    'Author' => 'Robin Jurinka',
    'AuthorUrl' => 'https://open.vanillaforums.com/profile/r_j',
    'License' => 'MIT'
];

/**
 * Plugin which adds a list of "Similar Tagged" discussions to the panel.
 *
 * A nw module is shown in the panel with a list of discussions. The discussions
 * are a "best match" based on the tags used. Not only the matching tags are
 * taken into consideration, but also the number of non-matching tags.
 * A discussion is higher prioritized if it has more matching tags and
 * few non-matchings tags.
 */
class SimilarTaggedPlugin extends Gdn_Plugin {
    /**
     * Init config with sane values.
     *
     * @return void.
     */
    public function setup() {
        touchConfig(
            [
                'similarTagged.AssetTarget' => 'Panel',
                'similarTagged.Limit' => 5
            ]
        );
    }

    /**
     * Create simple settings page.
     *
     * @param SettingsController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function settingsController_similarTagged_create($sender) {
        $sender->permission('Garden.Settings.Manage');

        $sender->addSideMenu('dashboard/settings/plugins');
        $sender->setData('Title', t('Similar Tagged Settings'));

        $configurationModule = new configurationModule($sender);
        $configurationModule->initialize(
            [
                'similarTagged.Limit' => [
                    'Default' => '5',
                    'LabelCode' => 'Discussion Limit',
                    'Description' => 'Number of similar tagged discussions to show in module',
                    'Options' => ['type' => 'number']
                ],
                'similarTagged.AssetTarget' => [
                    'Default' => 'Panel',
                    'LabelCode' => 'Asset Target',
                    'Description' => 'If your theme doesn\'t provide other options, "Panel" would most probably be correct',
                ],
            ]
        );
        $configurationModule->renderAll();
    }

    /**
     * Attach module to discussions.
     *
     * @param DiscussionController $sender Instance of the calling class.
     *
     * @return void.
     */
    public function discussionController_beforeDiscussionRender_handler($sender) {
        // If this discussion has no tags, there could be no similar
        // discussions shown.
        if (val('Tags', $sender->Discussion, false) === false) {
            return;
        }

        // Create module, set view, load data and attach to panel
        $similarTaggedModule = new SimilarTaggedModule($sender);
        $similarTaggedModule->setView($sender->fetchViewLocation('similartagged', '', 'plugins/similarTagged'));
        $similarTaggedModule->setData(
            'Discussions',
            $similarTaggedModule->getData($sender->Discussion->DiscussionID)
        );

        $sender->addModule($similarTaggedModule);
    }
}
