<?php

namespace Drupal\Tests\commerce_product\Functional;

use Drupal\Core\Language\Language;
use Drupal\Core\Url;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Tests translating products and variations.
 *
 * @group commerce
 */
class ProductTranslationTest extends ProductBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'config_translation',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_product_attribute',
      'administer languages',
      'administer content translation',
      'translate any entity',
      'translate configuration',
      'access content overview',
      'create content translations',
      'update content translations',
      'delete content translations',
    ], parent::getAdministratorPermissions());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add the French and German languages.
    $this->drupalGet('admin/config/regional/language/add');
    $this->submitForm(['predefined_langcode' => 'fr'], (string) $this->t('Add language'));
    $this->drupalGet('admin/config/regional/language/add');
    $this->submitForm(['predefined_langcode' => 'de'], (string) $this->t('Add language'));

    // Enable content translation on products and variations.
    $this->drupalGet('admin/config/regional/content-language');
    $edit = [
      'entity_types[commerce_product]' => TRUE,
      'settings[commerce_product][default][translatable]' => TRUE,
      'entity_types[commerce_product_variation]' => TRUE,
      'settings[commerce_product_variation][default][translatable]' => TRUE,
    ];
    $this->submitForm($edit, (string) $this->t('Save configuration'));
    // Adding languages requires a container rebuild in the test running
    // environment so that multilingual services are used.
    $this->resetAll();
  }

  /**
   * Test translating a product and its variations.
   */
  public function testProductTranslation() {
    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'Translation test product',
      'stores' => $this->stores,
    ]);
    $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'product_id' => $product->id(),
      'sku' => $this->randomMachineName(),
      'price' => new Price('9.99', 'USD'),
    ]);
    $this->drupalGet($product->toUrl('edit-form'));
    $this->getSession()->getPage()->clickLink('Translate');
    $this->assertSession()->linkByHrefExists("/product/{$product->id()}/translations/add/en/fr");
    $this->getSession()->getPage()->clickLink('Add');
    $this->getSession()->getPage()->fillField('Title', 'Produit de test de traduction');
    $this->getSession()->getPage()->pressButton('Save (this translation)');
    $this->assertSession()->pageTextContains('The product Produit de test de traduction has been successfully saved.');

    $this->drupalGet(Url::fromRoute('entity.commerce_product_variation.collection', [
      'commerce_product' => $product->id(),
    ]));
    $variation = $product->getVariations()[0];
    $translation_overview_url = $variation->toUrl('drupal:content-translation-overview');
    $this->assertSession()->linkByHrefExists($translation_overview_url->toString());
    $this->drupalGet($translation_overview_url);
    $this->assertSession()->linkByHrefExists('/fr/product/1/variations/1/translations/add/en/fr');
    $this->getSession()->getPage()->clickLink('Add');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Saved the Produit de test de traduction variation.');
  }

  /**
   * Test translating a single-variation product.
   */
  public function testSingleVariationProductTranslation() {
    $this->drupalGet('admin/commerce/config/product-types/default/edit');
    $edit = [
      'multipleVariations' => FALSE,
      'language_configuration[language_alterable]' => TRUE,
    ];
    $this->submitForm($edit, (string) $this->t('Save'));

    $this->drupalGet('admin/commerce/config/product-variation-types/default/edit');
    $edit = [
      'generateTitle' => FALSE,
    ];
    $this->submitForm($edit, (string) $this->t('Save'));

    $product = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'Translation test product',
      'stores' => $this->stores,
    ]);
    $variation = $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'product_id' => $product->id(),
      'title' => 'Hat',
      'sku' => $this->randomMachineName(),
      'price' => new Price('9.99', 'USD'),
    ]);
    $this->drupalGet($product->toUrl('edit-form'));
    $this->getSession()->getPage()->clickLink('Translate');
    $this->assertSession()->linkByHrefExists("/product/{$product->id()}/translations/add/en/fr");
    $this->getSession()->getPage()->clickLink('Add');
    $this->getSession()->getPage()->fillField('title[0][value]', 'Produit de test de traduction');
    $this->getSession()->getPage()->fillField('variations[entity][title][0][value]', 'Le Chapeau');
    $this->getSession()->getPage()->pressButton('Save (this translation)');
    $this->assertSession()->pageTextContains('The product Produit de test de traduction has been successfully saved.');

    // Confirm that the variation was translated together with the product.
    \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->resetCache([1]);
    $variation = ProductVariation::load(1);
    $this->assertEquals('en', $variation->language()->getId());
    $this->assertEquals('Hat', $variation->getTitle());
    $this->assertTrue($variation->hasTranslation('fr'));
    $translation = $variation->getTranslation('fr');
    $this->assertEquals('Le Chapeau', $translation->getTitle());

    // Edit the product and change the language to German.
    $this->drupalGet($product->toUrl('edit-form', ['language' => new Language(['id' => 'en'])]));
    $this->submitForm(['langcode[0][value]' => 'de'], 'Save');
    $this->assertSession()->pageTextContains('The product Translation test product has been successfully saved.');

    \Drupal::entityTypeManager()->getStorage('commerce_product')->resetCache([1]);
    $product = Product::load(1);
    $this->assertEquals('de', $product->language()->getId());
    $this->assertTrue($product->hasTranslation('fr'));

    // Confirm that the variation language was changed as well.
    \Drupal::entityTypeManager()->getStorage('commerce_product_variation')->resetCache([1]);
    $variation = ProductVariation::load(1);
    $this->assertEquals('de', $variation->language()->getId());
    $this->assertEquals('Hat', $variation->getTitle());
    $this->assertTrue($variation->hasTranslation('fr'));
  }

  /**
   * Test variation unpublishing when translation enabled.
   */
  public function testVariationTranslationUnpublishing() {
    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->createEntity('commerce_product', [
      'type' => 'default',
      'title' => 'Translation test product',
      'stores' => $this->stores,
    ]);
    $this->createEntity('commerce_product_variation', [
      'type' => 'default',
      'product_id' => $product->id(),
      'sku' => $this->randomMachineName(),
      'price' => new Price('9.99', 'USD'),
    ]);

    $this->drupalGet(Url::fromRoute('entity.commerce_product_variation.collection', [
      'commerce_product' => $product->id(),
    ]));
    $variation = $product->getVariations()[0];
    $translation_overview_url = $variation->toUrl('drupal:content-translation-overview');
    $this->assertSession()->linkByHrefExists($translation_overview_url->toString());
    $this->drupalGet($translation_overview_url);
    $this->assertSession()->linkByHrefExists('/fr/product/1/variations/1/translations/add/en/fr');
    $this->getSession()->getPage()->clickLink('Add');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Saved the Translation test product variation.');

    $this->drupalGet($translation_overview_url);

    $edit_url = $variation->toUrl('edit-form')->toString();
    $this->assertSession()->linkByHrefExists($edit_url);
    $this->getSession()->getPage()->clickLink('Edit');
    $this->getSession()->getPage()->uncheckField('Published');
    $this->getSession()->getPage()->pressButton('Save (this translation)');

    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
    $variation = $this->reloadEntity($variation);
    $this->assertFalse($variation->isPublished());
  }

}
