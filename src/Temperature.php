<?php

namespace CpuUtils;

class Temperature {

  public const IA32_THERM_STATUS         = 0x019c;
  public const IA32_TEMPERATURE_TARGET   = 0x01a2;
  public const IA32_PACKAGE_THERM_STATUS = 0x01b1;

  protected function __construct(
    /** @var int $cpuId cpu identifier */
    public readonly int $cpuId,
    /** @var float $cpu current cpu temperature */
    public readonly float $cpu,
    /** @var float $package current package temperature */
    public readonly float $package,
    /** @var int Maximum junction temperature */
    public readonly int $tjMax   = 100
  ) {}

  /**
   * Read the current temperature for a specific CPU.
   *
   * @param int $cpuId cpu identifier
   * @return self
   */
  public static function read(int $cpuId): self {
    $tjMaxMsr = MSR::read($cpuId, self::IA32_TEMPERATURE_TARGET);
    $tjMax    = ($tjMaxMsr->value >> 16) & 0xff;

    $cpuMsr     = MSR::read($cpuId, self::IA32_THERM_STATUS);
    $cpuReadout = ($cpuMsr->value >> 16) & 0xff;

    $packageMsr     = MSR::read($cpuId, self::IA32_PACKAGE_THERM_STATUS);
    $packageReadout = ($packageMsr->value >> 16) & 0xff;

    $cpu = $tjMax - $cpuReadout;
    $package = $tjMax - $packageReadout;

    return new self($cpuId, $cpu, $package, $tjMax);
  }

  public function toFahrenheit(): self {
    return new self(
      $this->cpuId,
      $this->cpu * 9 / 5 + 32,
      $this->package * 9 / 5 + 32,
      $this->tjMax * 9 / 5 + 32
    );
  }

}