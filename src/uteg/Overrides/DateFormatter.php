<?php
/**
 * Created by PhpStorm.
 * User: mathias
 * Date: 1/6/16
 * Time: 3:58 PM
 */

namespace uteg\Overrides;

use BCC\ExtraToolsBundle\Util\DateFormatter as Base;


class DateFormatter extends Base
{
    public function parse($date, $dateType = null, $timeType = null, $locale = null)
    {
        $result = new \DateTime();
        $result->setTimestamp($this->parseTimestamp($date, $dateType, $timeType, $locale));

        return $result;
    }

    public function getPattern($dateType = null, $timeType = null, $locale = null){
        $dateFormatter = \IntlDateFormatter::create($locale ?: \Locale::getDefault(), $this->formats[$dateType], $this->formats[$timeType], date_default_timezone_get(), \IntlDateFormatter::GREGORIAN);
        return $dateFormatter->getPattern();
    }

    private function parseTimestamp($date, $dateType = null, $timeType = null, $locale = null)
    {
        // try time default formats
        if ($dateType === null && $timeType === null) {
            foreach ($this->formats as $timeFormat) {
                // try date default formats
                foreach ($this->formats as $dateFormat) {
                    $dateFormatter = \IntlDateFormatter::create($locale ?: \Locale::getDefault(), $dateFormat, $timeFormat, date_default_timezone_get());
                    $timestamp = $dateFormatter->parse($date);

                    if ($dateFormatter->getErrorCode() == 0) {
                        return $timestamp;
                    }
                }


                // try other custom formats
                $formats = array(
                    'MMMM yyyy', // november 2011 - nov. 2011
                );
                foreach ($formats as $format) {
                    $dateFormatter = \IntlDateFormatter::create($locale ?: \Locale::getDefault(), $this->formats['none'], $this->formats['none'], date_default_timezone_get(), \IntlDateFormatter::GREGORIAN, $format);
                    $timestamp = $dateFormatter->parse($date);

                    if ($dateFormatter->getErrorCode() == 0) {
                        return $timestamp;
                    }
                }

                throw new \Exception('"' . $date . '" could not be converted to \DateTime');

            }
        } else {
            $dateFormatter = \IntlDateFormatter::create($locale ?: \Locale::getDefault(), $this->formats[$dateType], $this->formats[$timeType], date_default_timezone_get(), \IntlDateFormatter::GREGORIAN);
            $timestamp = $dateFormatter->parse($date);

            if ($dateFormatter->getErrorCode() == 0) {
                return $timestamp;
            }

            throw new \Exception('"' . $date . '" could not be converted to \DateTime');
        }

    }
}