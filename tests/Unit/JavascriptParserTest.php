<?php

namespace Tests\Unit;

use App\Beta8JavascriptFileParser;
use Tests\TestCase;

class JavascriptParserTest extends TestCase
{
    public function testV1_8ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.8.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '53c36ff59296abcdb969b63d509a671b',
                'size' => 36258,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => 'ad32aab7c2923ab6195a1abde8b1bc38',
                'size' => 4709,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '4460587d7b1ab89a47274709c00b2227',
                'size' => 3288,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'ca27d6971fd1b34f4279a5c6a30d87a1',
                'size' => 427452,
            ],
            [
                'id' => 'textformatter',
                'size' => 57891,
            ],
        ], $parser->coreSize());
    }

    public function testV1_8AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.8.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '8ee9564410acfcb48db33e2c89e08c55',
                'size' => 72980,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '4e6913a6826b8ba31c514cc5c7e7767a',
                'size' => 4709,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '1e316a1cdeda0f2b33fd80c32390c191',
                'size' => 823,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '8771841497cd63f01b9c823d767a5044',
                'size' => 356167,
            ],
        ], $parser->coreSize());
    }

    public function testV1_7ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.7.1-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => 'd54497b44c6f0c464b88e82e9ab8683a',
                'size' => 34998,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '8ed9bb95d4aebfc0305bbac6d1eaa72c',
                'size' => 4705,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '067e8f228d2e01783d757b6306d32020',
                'size' => 3341,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '30e719496b6af3576d58427623556979',
                'size' => 426175,
            ],
            [
                'id' => 'textformatter',
                'size' => 57891,
            ],
        ], $parser->coreSize());
    }

    public function testV1_7AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.7.1-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => 'cdf850de9c61991042ab831529336ed8',
                'size' => 72504,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '8dffa3fae3ff44381b4d0a025b6c3fb3',
                'size' => 4705,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '1e316a1cdeda0f2b33fd80c32390c191',
                'size' => 823,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '6bacd3c89aac40f57002a15f37a910b8',
                'size' => 350924,
            ],
        ], $parser->coreSize());
    }

    public function testV1_6ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.6.2-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '29a4d84541e84b736c222b523bb4a0ad',
                'size' => 30795,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '1ddf76fd6cff74620a166a185b3d6418',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '8d6448bc17b0c9c9acc44df09b9a37e1',
                'size' => 3301,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '65ca188696b18e8dae09bde8c4153c9a',
                'size' => 398307,
            ],
            [
                'id' => 'textformatter',
                'size' => 57868,
            ],
        ], $parser->coreSize());
    }

    public function testV1_6AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.6.2-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '79de17de4b7c25a92fac84c5928c1885',
                'size' => 53189,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => 'f6faab5d9b0976b333d617bfbf3eda08',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '1e316a1cdeda0f2b33fd80c32390c191',
                'size' => 823,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '57370588974a1e187c656a3497f78dee',
                'size' => 329990,
            ],
        ], $parser->coreSize());
    }

    public function testV1_5ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.5.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '29a4d84541e84b736c222b523bb4a0ad',
                'size' => 30795,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '1ddf76fd6cff74620a166a185b3d6418',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '8d6448bc17b0c9c9acc44df09b9a37e1',
                'size' => 3301,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '903356c565c82e0d3c6a44e550c425b4',
                'size' => 397903,
            ],
            [
                'id' => 'textformatter',
                'size' => 58534,
            ],
        ], $parser->coreSize());
    }

    public function testV1_5AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.5.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '79de17de4b7c25a92fac84c5928c1885',
                'size' => 53189,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => 'f6faab5d9b0976b333d617bfbf3eda08',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '1e316a1cdeda0f2b33fd80c32390c191',
                'size' => 823,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'c3f70117d274b204fba315ed6d0d74cf',
                'size' => 329821,
            ],
        ], $parser->coreSize());
    }

    public function testV1_4ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.4.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '29a4d84541e84b736c222b523bb4a0ad',
                'size' => 30795,
                'dev' => false,
            ],
            [
                'id' => 'flarum-sticky',
                'checksum' => '0fa33574cb078fd88453a455189ed2e0',
                'size' => 3362,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '1ddf76fd6cff74620a166a185b3d6418',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => 'b79da0b13ee04d09ef7b1676cd8e1d74',
                'size' => 11984,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '56751a734dbc8e02cf337b97facf5276',
                'size' => 396110,
            ],
            [
                'id' => 'textformatter',
                'size' => 64638,
            ],
        ], $parser->coreSize());
    }

    public function testV1_4AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.4.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '79de17de4b7c25a92fac84c5928c1885',
                'size' => 53189,
                'dev' => false,
            ],
            [
                'id' => 'flarum-sticky',
                'checksum' => '67525f10a8d3e030c5f81b42d1789662',
                'size' => 847,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => 'f6faab5d9b0976b333d617bfbf3eda08',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => '0118b3ce651cccc23f3062d9388cd9ac',
                'size' => 1297,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'fbb349d668bee03b73640615989ed823',
                'size' => 328513,
            ],
        ], $parser->coreSize());
    }

    public function testV1_3ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.3.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '846a851c0effe893e242fb61b9c36526',
                'size' => 30651,
                'dev' => false,
            ],
            [
                'id' => 'flarum-mentions',
                'checksum' => 'db03b46b5d689201a674066847f9b4d3',
                'size' => 17721,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '1ddf76fd6cff74620a166a185b3d6418',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '8d6448bc17b0c9c9acc44df09b9a37e1',
                'size' => 3301,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'a51d05b5a8f67394a0670595ed7339b8',
                'size' => 396064,
            ],
            [
                'id' => 'textformatter',
                'size' => 61113,
            ],
        ], $parser->coreSize());
    }

    public function testV1_3AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.3.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '38457833be10b9c514ad5934526f72d7',
                'size' => 53189,
                'dev' => false,
            ],
            [
                'id' => 'flarum-mentions',
                'checksum' => 'b8efed44245074019524f43a38a3906d',
                'size' => 931,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => 'f6faab5d9b0976b333d617bfbf3eda08',
                'size' => 4677,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '1e316a1cdeda0f2b33fd80c32390c191',
                'size' => 823,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '10e94240d3c29d41e9a35b439f6ad812',
                'size' => 328291,
            ],
        ], $parser->coreSize());
    }

    public function testV1_2ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.2.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '1f4b16168c3f5e4dd941af671ce63987',
                'size' => 29389,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '7ce80a4947d0a9d3ed59f1c983d6306c',
                'size' => 391876,
            ],
            [
                'id' => 'textformatter',
                'size' => 46873,
            ],
        ], $parser->coreSize());
    }

    public function testV1_2AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.2.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-tags',
                'checksum' => '746938ef3c28ade09d31da6bb658a39c',
                'size' => 52017,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'd2f45e06f2914e9347c95a440e31ac6c',
                'size' => 324971,
            ],
        ], $parser->coreSize());
    }

    public function testV1ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.0.0-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '9bfa334d419f1506dcaf3024ec9dec39',
                'size' => 376928,
            ],
            [
                'id' => 'textformatter',
                'size' => 58537,
            ],
        ], $parser->coreSize());
    }

    public function testV1AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/1.0.0-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '32493f26cf9fa701003391bd654a13e8',
                'size' => 310854,
            ],
        ], $parser->coreSize());
    }

    public function testBeta15ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta15-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '7a12f38e148ce80b9697ef91b9a84409',
                'size' => 354712,
            ],
            [
                'id' => 'textformatter',
                'size' => 66686,
            ],
        ], $parser->coreSize());
    }

    public function testBeta15AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta15-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => '62a602eba5dc4196f7e0dd29b512471e',
                'size' => 275387,
            ],
        ], $parser->coreSize());
    }

    public function testBeta14ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta14-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-approval',
                'checksum' => '116bad78bcf724266ad5464e7a1bfa59',
                'size' => 3335,
                'dev' => false,
            ],
            [
                'id' => 'flarum-emoji',
                'checksum' => '1a007ecc1380aa8462de57c98e99bc8a',
                'size' => 63192,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => '34a9dfa1a3264f2ce9ff33244bb47fe9',
                'size' => 12751,
                'dev' => false,
            ],
            [
                'id' => 'flarum-likes',
                'checksum' => '2d741ffea0ad7bf5a2c64e4e0ab31d16',
                'size' => 5005,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => 'd5c9ea357dfdf2bf30b26bbfdc9d62e7',
                'size' => 3906,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '8305acdddfca3c0ba4cf03a09ecfb3cf',
                'size' => 13778,
                'dev' => false,
            ],
            [
                'id' => 'flarum-mentions',
                'checksum' => '91dd3f66c6898acffdeb014b111f0b9f',
                'size' => 18793,
                'dev' => false,
            ],
            [
                'id' => 'flarum-sticky',
                'checksum' => '2a4914a7ee4f1989ed2f71ddaa94bcd0',
                'size' => 3656,
                'dev' => false,
            ],
            [
                'id' => 'flarum-subscriptions',
                'checksum' => '5ad2134add9cc43804e7be7380b893ce',
                'size' => 9005,
                'dev' => false,
            ],
            [
                'id' => 'flarum-suspend',
                'checksum' => '42d4518221e275e16ab38319936ab4a9',
                'size' => 5949,
                'dev' => false,
            ],
            [
                'id' => 'flarum-tags',
                'checksum' => 'def73acf2ef2902ecbebab5e0de60c16',
                'size' => 21984,
                'dev' => false,
            ],
            [
                'id' => 'fof-terms',
                'checksum' => '0262ab96571a6b82ccf94dc74bb606a4',
                'size' => 9469,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'aae54426912c20366d4da3ca82547b35',
                'size' => 354559,
            ],
            [
                'id' => 'textformatter',
                'size' => 67521,
            ],
        ], $parser->coreSize());
    }

    public function testBeta14AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta14-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-approval',
                'checksum' => 'b6c60e77dc5b430812b22ea594ebf196',
                'size' => 2275,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => 'ec6bf3345c4006b44c4f7ee1e17e14aa',
                'size' => 3190,
                'dev' => false,
            ],
            [
                'id' => 'flarum-likes',
                'checksum' => 'eecad784247ff9d978316757bb61dbe9',
                'size' => 1533,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => '70816389dcecb834be2a960acc45542a',
                'size' => 1520,
                'dev' => false,
            ],
            [
                'id' => 'flarum-statistics',
                'checksum' => 'f56eefe9457f233c8c0bb499291d864d',
                'size' => 51522,
                'dev' => false,
            ],
            [
                'id' => 'flarum-sticky',
                'checksum' => '8d5c24e2323495258a47177ad5497b6f',
                'size' => 1544,
                'dev' => false,
            ],
            [
                'id' => 'flarum-suspend',
                'checksum' => 'f2a7a51818cdbd6829bc5b615194d93b',
                'size' => 1527,
                'dev' => false,
            ],
            [
                'id' => 'flarum-tags',
                'checksum' => '1e3fbf4987b07de86dd2a3ea7c7c98f2',
                'size' => 52598,
                'dev' => false,
            ],
            [
                'id' => 'kilowhat-audit-free',
                'checksum' => '4918aac713933af48680059c15c85307',
                'size' => 12629,
                'dev' => false,
            ],
            [
                'id' => 'fof-terms',
                'checksum' => 'ffb1a774882f27b5442262ceff205699',
                'size' => 26548,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'c78da55dbdca2042269dbfc84ad1770b',
                'size' => 263487,
            ],
        ], $parser->coreSize());
    }

    public function testBeta13ForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta13-typical-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-approval',
                'checksum' => '48df22235ad1f69e427f97f2e4d43491',
                'size' => 3324,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => '679e48ddc9f5f750f92b8f5256c18473',
                'size' => 12339,
                'dev' => false,
            ],
            [
                'id' => 'flarum-subscriptions',
                'checksum' => 'ed265273f98f773de8e74b6822b19c80',
                'size' => 8518,
                'dev' => false,
            ],
            [
                'id' => 'clarkwinkelmann-circle-groups',
                'checksum' => '3bd5bf19f196e62cc073166230e34fc8',
                'size' => 7227,
                'dev' => true,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'c8e2250a2e9b2941eff58fd352462c89',
                'size' => 366635,
            ],
            [
                'id' => 'textformatter',
                'size' => 38641,
            ],
        ], $parser->coreSize());
    }

    public function testBeta13AdminParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta13-typical-admin.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-approval',
                'checksum' => 'b6c60e77dc5b430812b22ea594ebf196',
                'size' => 2278,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => 'cdd5e7802b1c922b53f4ce5000bef181',
                'size' => 3194,
                'dev' => false,
            ],
            [
                'id' => 'flarum-statistics',
                'checksum' => '22b71ada33d082ca17af4d1db33695fd',
                'size' => 51478,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'd09a0203b4a55d6e61ed8586f7615c01',
                'size' => 282908,

            ],
        ], $parser->coreSize());
    }

    public function testBeta13OptimizedForumParser()
    {
        $parser = new Beta8JavascriptFileParser(file_get_contents(__DIR__ . '/javascript-parser/beta13-optimized-forum.js'));

        $this->assertEquals([
            [
                'id' => 'flarum-akismet',
                'checksum' => '2f865450b51db0cdb86ea5175ab6ad84',
                'size' => 1811,
                'dev' => false,
            ],
            [
                'id' => 'flarum-approval',
                'checksum' => '48df22235ad1f69e427f97f2e4d43491',
                'size' => 3319,
                'dev' => false,
            ],
            [
                'id' => 'flarum-auth-facebook',
                'checksum' => 'f0db2e3f16cfded66fb8e486cd338953',
                'size' => 1651,
                'dev' => false,
            ],
            [
                'id' => 'flarum-auth-github',
                'checksum' => '1db7981e448fb0a8e55e83b8ea71f337',
                'size' => 1635,
                'dev' => false,
            ],
            [
                'id' => 'flarum-auth-twitter',
                'checksum' => '6184ee23d2b76d6bd18ae752bc9f40ab',
                'size' => 1643,
                'dev' => false,
            ],
            [
                'id' => 'flarum-emoji',
                'checksum' => '4760827951e5621c5895cbb1f74860c8',
                'size' => 60106,
                'dev' => false,
            ],
            [
                'id' => 'flarum-flags',
                'checksum' => '679e48ddc9f5f750f92b8f5256c18473',
                'size' => 12334,
                'dev' => false,
            ],
            [
                'id' => 'flarum-likes',
                'checksum' => 'bd3a8e53adaea330dbcb313e8a299870',
                'size' => 4967,
                'dev' => false,
            ],
            [
                'id' => 'flarum-lock',
                'checksum' => 'd1f6d44dae8c4580470142bca836915f',
                'size' => 3908,
                'dev' => false,
            ],
            [
                'id' => 'flarum-markdown',
                'checksum' => '63cddc5d79b0a789f8d2012b4f8613c3',
                'size' => 13397,
                'dev' => false,
            ],
            [
                'id' => 'flarum-mentions',
                'checksum' => 'df8634de1196af9dd57d306339937f1f',
                'size' => 18342,
                'dev' => false,
            ],
            [
                'id' => 'flarum-sticky',
                'checksum' => 'a2e54f8082a3a0eca3d2adb3a3d44858',
                'size' => 3648,
                'dev' => false,
            ],
            [
                'id' => 'flarum-subscriptions',
                'checksum' => 'ed265273f98f773de8e74b6822b19c80',
                'size' => 8513,
                'dev' => false,
            ],
            [
                'id' => 'flarum-suspend',
                'checksum' => 'b193d4eb8e4b08d46c428437fcaf3cdd',
                'size' => 5836,
                'dev' => false,
            ],
            [
                'id' => 'flarum-tags',
                'checksum' => '0579e32c85209f9e88244999f117ab90',
                'size' => 21874,
                'dev' => false,
            ],
            [
                'id' => 'fof-user-bio',
                'checksum' => '50a15d0866765149a16449f445a1a166',
                'size' => 3992,
                'dev' => false,
            ],
            [
                'id' => 'fof-spamblock',
                'checksum' => 'ca675097cba4d45b3796be15ff50d9a5',
                'size' => 1990,
                'dev' => false,
            ],
            [
                'id' => 'fof-merge-discussions',
                'checksum' => '9f612cfb7d841f1f6d4b6fc3d61d4ad9',
                'size' => 15204,
                'dev' => false,
            ],
            [
                'id' => 'fof-links',
                'checksum' => '24ec433e61bf4bb490ab85722040ee57',
                'size' => 4322,
                'dev' => false,
            ],
            [
                'id' => 'fof-split',
                'checksum' => 'bbaa19e0e143452a1b3a9c5ea25a9824',
                'size' => 5140,
                'dev' => false,
            ],
            [
                'id' => 'fof-byobu',
                'checksum' => '2d81c74038045a0646659c12b8d417be',
                'size' => 32335,
                'dev' => false,
            ],
            [
                'id' => 'fof-ban-ips',
                'checksum' => 'd8a07ded549c3b2e4c679fdd8c1f6daa',
                'size' => 12018,
                'dev' => false,
            ],
            [
                'id' => 'fof-username-request',
                'checksum' => '021b9c065d8174c740f50e32f9bab4b3',
                'size' => 14619,
                'dev' => false,
            ],
            [
                'id' => 'fof-geoip',
                'checksum' => '68fddd5182898769e8f6055f03a556d5',
                'size' => 14756,
                'dev' => false,
            ],
            [
                'id' => 'askvortsov-moderator-warnings',
                'checksum' => '16cd041bfcabfb9c330092654229fb96',
                'size' => 18117,
                'dev' => false,
            ],
            [
                'id' => 'fof-best-answer',
                'checksum' => '1b81e968daef18f3a108292dec77a332',
                'size' => 8413,
                'dev' => false,
            ],
            [
                'id' => 'fof-prevent-necrobumping',
                'checksum' => '961fbb6a8b39a976aa45c18dd0bdd9ba',
                'size' => 21094,
                'dev' => false,
            ],
            [
                'id' => 'fof-drafts',
                'checksum' => '8c92d4934fe295e4d3242b218ef336b7',
                'size' => 18068,
                'dev' => false,
            ],
            [
                'id' => 'kyrne-websocket',
                'checksum' => '399ef6f6e582442c4d7addd9b64600b3',
                'size' => 120290,
                'dev' => false,
            ],
            [
                'id' => 'kilowhat-audit-pro',
                'checksum' => '62923f65bb25cd2337b9647ddb9945ca',
                'size' => 12846,
                'dev' => false,
            ],
            [
                'id' => 'askvortsov-discussion-templates',
                'checksum' => '7de94b10cb6491469ff9bcf578727147',
                'size' => 2247,
                'dev' => false,
            ],
            [
                'id' => 'fof-nightmode',
                'checksum' => '4377b75994bb07213e7b50e9b9a31964',
                'size' => 7049,
                'dev' => false,
            ],
        ], $parser->extensions());

        $this->assertEquals([
            [
                'id' => 'core',
                'checksum' => 'c8e2250a2e9b2941eff58fd352462c89',
                'size' => 366582,
            ],
            [
                'id' => 'textformatter',
                'size' => 135028,
            ],
        ], $parser->coreSize());
    }
}
