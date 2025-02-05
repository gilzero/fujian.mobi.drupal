<?php

namespace Drupal\Tests\estimated_read_time\Kernel;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\estimated_read_time\Service\ReadTimeAdapter;
use Drupal\estimated_read_time\Service\ReadTimeAdapterInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\Tests\EntityViewTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Provides tests for estimated read time.
 */
class EstimatedReadTimeTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use EntityViewTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'datetime',
    'user',
    'system',
    'filter',
    'field',
    'text',
    'estimated_read_time',
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig('filter');
    $this->installConfig('node');
    $this->installConfig('estimated_read_time');

    $this->createContentType([
      'type' => 'page',
      'name' => 'Page',
    ]);

    $fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_estimated_read_time',
      'entity_type' => 'node',
      'type' => 'estimated_read_time',
    ]);
    $fieldStorage->save();

    FieldConfig::create([
      'field_storage' => $fieldStorage,
      'label' => 'Read Time',
      'bundle' => 'page',
      'settings' => [
        'view_mode' => 'default',
        'words_per_minute' => 100,
      ],
      'translatable' => TRUE,
    ])->save();

    /** @var \Drupal\Core\Field\FormatterPluginManager $fieldFormatter */
    $fieldFormatter = $this->container->get('plugin.manager.field.formatter');
    $component = $fieldFormatter->prepareConfiguration('estimated_read_time', []);

    EntityViewDisplay::load('node.page.default')
      ->setComponent('field_estimated_read_time', $component)
      ->save();

    DateFormat::create([
      'id' => 'fallback',
      'label' => 'Fallback',
      'pattern' => 'Y-m-d',
    ])->save();

    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('es')->save();

    $readTimeAdapter = $this->getReadTimeAdapterMock();
    $this->container->set('estimated_read_time.read_time_adapter', $readTimeAdapter);
  }

  /**
   * Tests the node estimated read time functionality.
   *
   * @dataProvider estimatedReadTimeProvider
   */
  public function testEstimatedReadTime(string $body, array $minutes, array $seconds): void {

    // Make the translation shorter.
    $translatedBody = substr($body, 0, (int) floor(strlen($body) / 1.75));

    // Create a node with one translation and test the estimated read time.
    $node = Node::create(['type' => 'page', 'body' => $body, 'title' => 'Test Page Read Time']);
    $node->addTranslation('de', ['title' => 'Test Lesezeit', 'body' => $translatedBody]);
    $node->save();
    $this->assertNotEmpty(Node::load($node->id()), 'The node was created successfully.');

    $estimatedReadTime = $node->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals($minutes['en'], $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly.');
    $this->assertEquals($seconds['en'], $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly.');

    // Test the estimated read time for the created translation.
    $translation = $node->getTranslation('de');
    $this->assertNotEmpty($translation, 'The created translation was loaded successfully.');

    $estimatedReadTime = $translation->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals($minutes['de'], $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for the created translation.');
    $this->assertEquals($seconds['de'], $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for the created translation.');

    // Test the formatting for rendered content.
    $content = $this->buildEntityView($node);
    $this->assertNotEmpty($content['field_estimated_read_time'][0]);
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    $this->setRawContent($renderer->renderInIsolation($content));

    if ($minutes['en'] === 0) {
      $this->assertNoRaw('min read');
    }
    else {
      $this->assertRaw($minutes['en'] . ' min read');
    }

    // Test the estimated read time when adding a translation to the node.
    $node->addTranslation('es', ['title' => 'Página', 'body' => $translatedBody]);
    $node->save();
    $translation = $node->getTranslation('es');
    $this->assertNotEmpty($translation, 'The added translation was loaded successfully.');

    $estimatedReadTime = $translation->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals($minutes['es'], $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for the added translation.');
    $this->assertEquals($seconds['es'], $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for the added translation.');

    // Test how the read time changes, when the field configuration is changed.
    // Here, only the case with full body is considered. It is necessary to
    // create a new node, because the field definitions are already attached
    // to the first node.
    $field = FieldConfig::load('node.page.field_estimated_read_time')
      ->setSetting('words_per_minute', 50);
    $field->save();

    $otherNode = Node::create(['type' => 'page', 'title' => 'Other page slow reader', 'body' => $body]);
    $otherNode->save();
    $this->assertNotEmpty(Node::load($otherNode->id()), 'The other node was created successfully.');

    $estimatedReadTime = $otherNode->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals($minutes['slow'], $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly.');
    $this->assertEquals($seconds['slow'], $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly.');

    $otherNode->save();
  }

  /**
   * Tests the node estimated read time functionality.
   *
   * @dataProvider estimatedReadTimeProvider
   */
  public function testEstimatedReadTimeManual(string $body, array $minutes, array $seconds): void {

    // Create a node with one translation.
    $node = Node::create(['type' => 'page', 'body' => $body, 'title' => 'Test Page Read Time']);
    $node->set('field_estimated_read_time', [
      'minutes' => 60,
      'seconds' => 30,
      'auto' => 0,
    ]);
    $node->addTranslation('de', ['title' => 'Test Lesezeit', 'body' => $body]);
    $node->save();
    $this->assertNotEmpty(Node::load($node->id()), 'The node with manual read time was created successfully.');

    // Alter manual read time for translation.
    $translation = $node->getTranslation('de');
    $translation->set('field_estimated_read_time', [
      'minutes' => 55,
      'seconds' => 55,
      'auto' => 0,
    ]);
    $translation->save();

    // Test manual read time for original node.
    $estimatedReadTime = $node->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals(60, $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for manual setting.');
    $this->assertEquals(30, $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for manual setting.');

    // Test manual read time for translated node.
    $estimatedReadTime = $translation->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals(55, $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for manual setting.');
    $this->assertEquals(55, $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for manual setting.');

    // Set to automatic read time estimation for original node.
    $node->set('field_estimated_read_time', ['auto' => 1]);
    $node->save();

    // Test manual read time for original node.
    $estimatedReadTime = $node->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals($minutes['en'], $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for manual setting.');
    $this->assertEquals($seconds['en'], $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for manual setting.');

    // Manual read time for translated node should stay the same.
    $estimatedReadTime = $translation->get('field_estimated_read_time')->first()?->getValue();
    $this->assertEquals(55, $estimatedReadTime['minutes'] ?? NULL, 'The estimated read time minutes are calculated correctly for manual setting.');
    $this->assertEquals(55, $estimatedReadTime['seconds'] ?? NULL, 'The estimated read time seconds are calculated correctly for manual setting.');

  }

  /**
   * Data provider for testEstimatedReadTime.
   *
   * @return array
   *   A list of test scenarios.
   */
  public function estimatedReadTimeProvider(): array {

    // The read time is close to zero, when the body is empty. Note that there
    // is some miscellaneous content such as the title of the node and the
    // label of the estimated read time field. Therefore, the read time is five
    // seconds.
    $cases['empty'] = [
      'body' => '',
      'minutes' => ['en' => 0, 'de' => 0, 'es' => 0, 'slow' => 0],
      'seconds' => ['en' => 5, 'de' => 4, 'es' => 4, 'slow' => 10],
    ];

    // In the read time adapter, the estimate() method is mocked to divide the
    // word count by the configured words per minute. The body is manipulated
    // slightly for translated content to emulate different text lengths in
    // different languages. The slow reader is used to test changing the field
    // settings.
    $cases['full-body'] = [
      'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. At augue eget arcu dictum varius duis at consectetur lorem. Mauris in aliquam sem fringilla ut. Odio ut sem nulla pharetra diam sit amet. Ipsum faucibus vitae aliquet nec ullamcorper sit. Id eu nisl nunc mi ipsum faucibus vitae aliquet nec. Tristique sollicitudin nibh sit amet commodo nulla facilisi. Etiam erat velit scelerisque in. Blandit massa enim nec dui. Dictum fusce ut placerat orci nulla pellentesque dignissim. Ridiculus mus mauris vitae ultricies leo integer malesuada nunc vel. Pellentesque id nibh tortor id aliquet lectus. Sit amet aliquam id diam maecenas ultricies. Nibh mauris cursus mattis molestie a. Consectetur adipiscing elit duis tristique sollicitudin nibh sit amet. In mollis nunc sed id semper. Non arcu risus quis varius quam. Ullamcorper sit amet risus nullam eget felis eget nunc lobortis. Sed elementum tempus egestas sed sed risus pretium quam. Pharetra pharetra massa massa ultricies mi quis hendrerit. Feugiat in fermentum posuere urna nec tincidunt praesent semper. Cras ornare arcu dui vivamus arcu felis bibendum ut tristique. Porta nibh venenatis cras felis. Nunc sed augue.',
      'minutes' => ['en' => 2, 'de' => 1, 'es' => 1, 'slow' => 4],
      'seconds' => ['en' => 0, 'de' => 10, 'es' => 10, 'slow' => 0],
    ];

    return $cases;
  }

  /**
   * Mocks the read time adapter.
   */
  public function getReadTimeAdapterMock(): ReadTimeAdapterInterface {
    $readTimeAdapter = $this->getMockBuilder(ReadTimeAdapter::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['estimate'])
      ->getMock();
    $readTimeAdapter->method('estimate')
      ->willReturnCallback(function ($content, $wordsPerMinute) {
        $estimate = str_word_count(strip_tags($content)) / $wordsPerMinute;
        $minutes = floor($estimate);
        return [
          'minutes' => (int) $minutes,
          'seconds' => (int) (($estimate - $minutes) * 60),
        ];
      });
    return $readTimeAdapter;
  }

}
