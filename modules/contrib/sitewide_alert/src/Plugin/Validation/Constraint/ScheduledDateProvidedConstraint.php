<?php

declare(strict_types=1);

namespace Drupal\sitewide_alert\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;

/**
 * Plugin implementation of the 'ScheduledDateProvided'.
 */
#[Constraint(
  id: 'ScheduledDateProvided',
  label: new TranslatableMarkup('Scheduled date provided constraint', [], ['context' => 'Validation']),
  type: ['entity', 'sitewide_alert']
)]
class ScheduledDateProvidedConstraint extends CompositeConstraintBase {

  /**
   * Message shown when entity is marked as scheduled without a scheduled date.
   *
   * @var string
   */
  public string $messageDatesNotProvided = 'This alert is marked as scheduled, but scheduled dates are not provided.';

  /**
   * An array of entity fields which should be passed to the validator.
   *
   * @return string[]
   *   An array of field names.
   */
  public function coversFields(): array {
    return ['scheduled_alert', 'scheduled_date'];
  }

}
