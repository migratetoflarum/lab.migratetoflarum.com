<?php

namespace Tests\Unit;

use App\FlarumVersion;
use App\FlarumVersionGuesser;
use Illuminate\Support\Arr;
use Tests\TestCase;

class FlarumVersionGuesserTest extends TestCase
{
    /**
     * @var $guesser FlarumVersionGuesser
     */
    protected $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = new FlarumVersionGuesser();
    }

    protected function homepageScript(int $flarumVersion, bool $withTagsExtension, bool $canSeeDiscussions): string
    {
        $isBeta7 = $flarumVersion === 7;

        $documentName = $isBeta7 ? 'document' : 'apiDocument';

        $discussionCreatedAtName = $isBeta7 ? 'startTime' : 'createdAt';
        $discussionLastPostedAtAtName = $isBeta7 ? 'lastTime' : 'lastPostedAt';
        $discussionStartUserRelationName = $isBeta7 ? 'startUser' : 'user';
        $discussionLastUserRelationName = $isBeta7 ? 'lastUser' : 'lastPostedUser';
        $discussionFirstPostRelationName = $isBeta7 ? 'startPost' : 'firstPost';

        $postCreatedAtName = $isBeta7 ? 'time' : 'createdAt';

        $tagLastPostedAtName = $isBeta7 ? 'lastTime' : 'lastPostedAt';

        $iconPrefix = $isBeta7 ? '' : 'fas fa-';

        $userAttributes = $isBeta7 ? [
            'username',
            'avatarUrl',
        ] : [
            'username',
            'displayName',
            'avatarUrl',
        ];

        $postAttributes = array_merge($flarumVersion < 9 ? [
            'id',
        ] : [], [
            'number',
            $postCreatedAtName,
            'contentType',
            'contentHtml',
        ], $isBeta7 ? [
            'isApproved',
        ] : []);

        if ($withTagsExtension) {
            $tagsInclude = [
                [
                    'type' => 'tags',
                    'id' => '1',
                    'attributes' => array_merge([
                        'name' => 'General',
                        'description' => null,
                        'slug' => 'general',
                        'color' => '#888',
                        'backgroundUrl' => null,
                        'backgroundMode' => null,
                    ], $flarumVersion >= 9 ? [
                        'icon' => null,
                    ] : [], [
                        'iconUrl' => null,
                        'discussionCount' => 1,
                        'position' => 0,
                        'defaultSort' => null,
                        'isChild' => false,
                        'isHidden' => false,
                        $tagLastPostedAtName => '2019-07-05T16:08:00+00:00',
                        'canStartDiscussion' => false,
                        'canAddToDiscussion' => false,
                    ]),
                ],
            ];
        } else {
            $tagsInclude = [];
        }

        $payload = [
            'resources' => array_merge([
                [
                    'type' => 'forums',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'Flarum',
                        'description' => 'Just a random test forum for unit tests',
                        'showLanguageSelector' => true,
                        'baseUrl' => 'https://example.com',
                        'basePath' => '',
                        'debug' => false,
                        'apiUrl' => 'https://example.com/api',
                        'welcomeTitle' => 'Welcome to Flarum',
                        'welcomeMessage' => 'This is beta software and you should not use it in production.',
                        'themePrimaryColor' => '#4D698E',
                        'themeSecondaryColor' => '#4D698E',
                        'logoUrl' => null,
                        'faviconUrl' => null,
                        'headerHtml' => null,
                        'footerHtml' => null,
                        'allowSignUp' => true,
                        'defaultRoute' => '/all',
                        'canViewDiscussions' => true,
                        'canStartDiscussion' => false,
                        'canViewUserList' => false,
                        'canViewFlags' => false,
                        'guidelinesUrl' => null,
                        'minPrimaryTags' => '1',
                        'maxPrimaryTags' => '1',
                        'minSecondaryTags' => '0',
                        'maxSecondaryTags' => '3',
                    ],
                ],
                [
                    'type' => 'groups',
                    'id' => '1',
                    'attributes' => [
                        'nameSingular' => 'Admin',
                        'namePlural' => 'dmins',
                        'color' => '#B72A2A',
                        'icon' => $iconPrefix . 'wrench'
                    ],
                ],
                [
                    'type' => 'groups',
                    'id' => '1',
                    'attributes' => [
                        'nameSingular' => 'Admin',
                        'namePlural' => 'Admins',
                        'color' => '#B72A2A',
                        'icon' => $iconPrefix . 'wrench',
                    ],
                ],
                [
                    'type' => 'groups',
                    'id' => '2',
                    'attributes' => [
                        'nameSingular' => 'Guest',
                        'namePlural' => 'Guests',
                        'color' => null,
                        'icon' => null,
                    ],
                ],
                [
                    'type' => 'groups',
                    'id' => '3',
                    'attributes' => [
                        'nameSingular' => 'Member',
                        'namePlural' => 'Members',
                        'color' => null,
                        'icon' => null,
                    ],
                ],
                [
                    'type' => 'groups',
                    'id' => '4',
                    'attributes' => [
                        'nameSingular' => 'Mod',
                        'namePlural' => 'Mods',
                        'color' => '#80349E',
                        'icon' => $iconPrefix . 'bolt',
                    ],
                ],
            ], $tagsInclude),
            'session' => [
                'userId' => 0,
                'csrfToken' => 'abcdefghijklmnopqrstABCDEFGHIJKLMNOPQRST',
            ],
            'locales' => [ // In beta 7 `locales` and `locale` seem to be at the end of the payload, but we won't replicate it
                'en' => 'English',
            ],
            'locale' => 'en',
            $documentName => [
                'links' => [
                    'first' => 'https://example.com/api/discussions?sort=something',
                ],
                'data' => $canSeeDiscussions ? [
                    [
                        'type' => 'discussions',
                        'id' => '1',
                        'attributes' => [
                            'title' => 'A first discussion',
                            'slug' => 'a-first-discussion',
                            'commentCount' => 1,
                            'participantCount' => 1,
                            $discussionCreatedAtName => '2019-07-05T16:08:00+00:00',
                            $discussionLastPostedAtAtName => '2019-07-05T16:08:00+00:00',
                            'lastPostNumber' => 1,
                            'canReply' => false,
                            'canRename' => false,
                            'canDelete' => false,
                            'canHide' => false,
                            'isApproved' => true,
                            'isLocked' => false,
                            'canLock' => false,
                            'isSticky' => false,
                            'canSticky' => false,
                            'canTag' => false,
                        ],
                        'relationships' => array_merge([
                            $discussionStartUserRelationName => [
                                'data' => [
                                    'type' => 'users',
                                    'id' => '1',
                                ],
                            ],
                            $discussionLastUserRelationName => [
                                'data' => [
                                    'type' => 'users',
                                    'id' => '1',
                                ],
                            ],
                            $discussionFirstPostRelationName => [
                                'data' => [
                                    'type' => 'posts',
                                    'id' => '1',
                                ],
                            ],
                        ], $withTagsExtension ? [
                            'tags' => [
                                'data' => [
                                    [
                                        'type' => 'posts',
                                        'id' => '1',
                                    ],
                                ],
                            ],
                        ] : []),
                    ]
                ] : [],
                'included' => $canSeeDiscussions ? array_merge([
                    [
                        'type' => 'users',
                        'id' => '1',
                        'attributes' => Arr::only([
                            'username' => 'Admin',
                            'displayName' => 'Admin',
                            'avatarUrl' => null,
                        ], $userAttributes),
                    ], [
                        'type' => 'posts',
                        'id' => '1',
                        'attributes' => Arr::only([
                            'id' => 1,
                            'number' => 1,
                            $postCreatedAtName => '2018-01-23T23:37:49+00:00',
                            'contentType' => 'comment',
                            'contentHtml' => '<p>Hello world</p>',
                        ], $postAttributes),
                    ],
                ], $tagsInclude) : [],
            ],
        ];

        if ($isBeta7) {
            return '
        document.getElementById(\'flarum-loading\').style.display = \'none\';
                  var app = System.get(\'flarum/app\').default;
          var modules = ["locale","sijad\/pages\/main","flarum\/approval\/main","flarum\/emoji\/main","flarum\/auth\/facebook\/main","flagrow\/byobu\/main","flagrow\/masquerade\/main","flagrow\/paywall\/main","flarum\/flags\/main","flarum\/likes\/main","flarum\/lock\/main","flarum\/mentions\/main","reflar\/reactions\/main","flarum\/sticky\/main","flarum\/subscriptions\/main","flarum\/suspend\/main","flarum\/tags\/main"];

          for (var i in modules) {
            var module = System.get(modules[i]);
            if (module.default) module.default(app);
          }

          app.boot(' . json_encode($payload) . ');
              ';
        }

        $htmlAttributes = '';

        // Beta 10 adds (back) the dir and lang attributes https://github.com/flarum/core/commit/e88a9394edccc992b9b5fa2970086d2c4df86b8a
        if ($flarumVersion >= 10) {
            $htmlAttributes = ' dir="ltr" lang="en"';
        }

        return '<!doctype html>
            <html' . $htmlAttributes . '>
            document.getElementById(\'flarum-loading\').style.display = \'none\';

            try {
                flarum.core.app.load(' . json_encode($payload) . ');
                flarum.core.app.bootExtensions(flarum.extensions);
                flarum.core.app.boot();
            } catch (e) {
                var error = document.getElementById(\'flarum-loading-error\');
                error.innerHTML += document.getElementById(\'flarum-content\').textContent;
                error.style.display = \'block\';
                throw e;
            }
        ';
    }

    function testGarbage()
    {
        $html = '<p>Hello world this is just a random webpage and certainly not Flarum</p>';

        $this->assertEquals([], $this->guesser->guess($html, $html));

        $html = 'console.log("stuff");';

        $this->assertEquals([], $this->guesser->guess($html, $html));
    }

    function testBeta7Normal()
    {
        $html = $this->homepageScript(7, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_7,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta7NoDiscussions()
    {
        $html = $this->homepageScript(7, true, false);

        $this->assertEquals([
            FlarumVersion::BETA_7,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta7Private()
    {
        $html = $this->homepageScript(7, false, false);

        $this->assertEquals([
            FlarumVersion::BETA_7,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta8Normal()
    {
        $html = $this->homepageScript(8, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_8,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta8NoDiscussions()
    {
        $html = $this->homepageScript(8, true, false);

        $this->assertEquals([
            FlarumVersion::BETA_8,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta8Private()
    {
        $html = $this->homepageScript(8, false, false);

        $this->assertEquals([
            FlarumVersion::BETA_8,
            FlarumVersion::BETA_9,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta9Normal()
    {
        $html = $this->homepageScript(9, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_9,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta9NoDiscussions()
    {
        $html = $this->homepageScript(9, true, false);

        $this->assertEquals([
            FlarumVersion::BETA_9,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta9Private()
    {
        $html = $this->homepageScript(9, false, false);

        $this->assertEquals([
            FlarumVersion::BETA_8,
            FlarumVersion::BETA_9,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta10Normal()
    {
        $html = $this->homepageScript(10, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_10,
            FlarumVersion::BETA_11,
            FlarumVersion::BETA_12,
            FlarumVersion::BETA_13,
            FlarumVersion::BETA_14,
            FlarumVersion::BETA_14_1,
            FlarumVersion::BETA_15,
            FlarumVersion::BETA_16,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta11Normal()
    {
        $html = $this->homepageScript(11, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_10,
            FlarumVersion::BETA_11,
            FlarumVersion::BETA_12,
            FlarumVersion::BETA_13,
            FlarumVersion::BETA_14,
            FlarumVersion::BETA_14_1,
            FlarumVersion::BETA_15,
            FlarumVersion::BETA_16,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta12Normal()
    {
        $html = $this->homepageScript(12, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_10,
            FlarumVersion::BETA_11,
            FlarumVersion::BETA_12,
            FlarumVersion::BETA_13,
            FlarumVersion::BETA_14,
            FlarumVersion::BETA_14_1,
            FlarumVersion::BETA_15,
            FlarumVersion::BETA_16,
        ], $this->guesser->guess($html, $html));
    }

    function testBeta13Normal()
    {
        $html = $this->homepageScript(13, true, true);

        $this->assertEquals([
            FlarumVersion::BETA_10,
            FlarumVersion::BETA_11,
            FlarumVersion::BETA_12,
            FlarumVersion::BETA_13,
            FlarumVersion::BETA_14,
            FlarumVersion::BETA_14_1,
            FlarumVersion::BETA_15,
            FlarumVersion::BETA_16,
        ], $this->guesser->guess($html, $html));
    }
}
