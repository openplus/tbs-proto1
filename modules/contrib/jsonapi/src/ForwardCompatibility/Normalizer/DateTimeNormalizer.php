<?php

namespace Drupal\jsonapi\ForwardCompatibility\Normalizer;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\TypedData\Type\DateTimeInterface;
use Drupal\jsonapi\Normalizer\NormalizerBase;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Converts values for datetime objects to RFC3339 and from common formats.
 *
 * @internal JSON:API maintains no PHP API. The API is the HTTP API. This class
 *   may change at any time and could break any dependencies on it.
 *
 * @see https://www.drupal.org/project/jsonapi/issues/3032787
 * @see jsonapi.api.php
 *
 * @see \Drupal\serialization\Normalizer\DateTimeNormalizer
 * @todo Remove when JSON:API requires a version of Drupal core that includes https://www.drupal.org/project/drupal/issues/2926508.
 */
class DateTimeNormalizer extends NormalizerBase implements DenormalizerInterface {

  /**
   * Allowed datetime formats for the denormalizer.
   *
   * The list is chosen to be unambiguous and language neutral, but also common
   * for data interchange.
   *
   * @var string[]
   *
   * @see http://php.net/manual/en/datetime.createfromformat.php
   */
  protected $allowedFormats = [
    'RFC 3339' => \DateTime::RFC3339,
    'ISO 8601' => \DateTime::ISO8601,
  ];

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = DateTimeInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($datetime, $format = NULL, array $context = []) {
    // @todo Remove when JSON:API only supports Drupal >=8.7, which fixed this in https://www.drupal.org/project/drupal/issues/3002164.
    $drupal_date_time = floatval(floatval(\Drupal::VERSION) >= 8.7)
      ? $datetime->getDateTime()
      : ($datetime->getValue() ? new DrupalDateTime($datetime->getValue(), 'UTC') : NULL);
    if ($drupal_date_time === NULL) {
      return $drupal_date_time;
    }
    return $drupal_date_time
      // Set an explicit timezone. Otherwise, timestamps may end up being
      // normalized using the user's preferred timezone. Which would result in
      // many variations and complex caching.
      // @see \Drupal\Core\Datetime\DrupalDateTime::prepareTimezone()
      // @see drupal_get_user_timezone()
      ->setTimezone($this->getNormalizationTimezone())
      ->format(\DateTime::RFC3339);
  }

  /**
   * Gets the timezone to be used during normalization.
   *
   * @see ::normalize
   *
   * @returns \DateTimeZone
   *   The timezone to use.
   */
  protected function getNormalizationTimezone() {
    $default_site_timezone = \Drupal::config('system.date')->get('timezone.default');
    return new \DateTimeZone($default_site_timezone);
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    // This only knows how to denormalize datetime strings and timestamps. If
    // something else is received, let validation constraints handle this.
    if (!is_string($data) && !is_numeric($data)) {
      return $data;
    }

    // Loop through the allowed formats and create a \DateTime from the
    // input data if it matches the defined pattern. Since the formats are
    // unambiguous (i.e., they reference an absolute time with a defined time
    // zone), only one will ever match.
    $allowed_formats = isset($context['datetime_allowed_formats'])
      ? $context['datetime_allowed_formats']
      : $this->allowedFormats;
    foreach ($allowed_formats as $format) {
      $date = \DateTime::createFromFormat($format, $data);
      $errors = \DateTime::getLastErrors();
      if ($date !== FALSE && empty($errors['errors']) && empty($errors['warnings'])) {
        return $date;
      }
    }

    $format_strings = [];

    foreach ($allowed_formats as $label => $format) {
      $format_strings[] = "\"$format\" ($label)";
    }

    $formats = implode(', ', $format_strings);
    throw new UnexpectedValueException(sprintf('The specified date "%s" is not in an accepted format: %s.', $data, $formats));
  }

}
