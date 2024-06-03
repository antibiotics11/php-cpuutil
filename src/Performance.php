<?php

namespace CpuUtils;

class Performance {

  public const IA32_PERF_STATUS = 0x0198;
  public const IA32_PERF_CTL    = 0x0199;

  protected function __construct(
    /** @var int $cpuId cpu identifier */
    public readonly int   $cpuId,
    /** @var int $performanceState current performance state */
    public readonly int   $performanceState,
    /** @var int $performanceControl current performance control state */
    public readonly int   $performanceControl,
    /** @var float $frequency current cpu frequency in MHz */
    public readonly float $frequency
  ) {}

  /**
   * Read the current performance status of a specific CPU.
   *
   * @param int   $cpuId         cpu identifier
   * @param float $baseFrequency base frequency of the cpu in MHz
   * @return self
   */
  public static function read(int $cpuId, float $baseFrequency = 100.0): self {
    $perfStatusMsr    = MSR::read($cpuId, self::IA32_PERF_STATUS);
    $performanceState = $perfStatusMsr->value;
    $frequency        = (($performanceState >> 8) & 0xffff) * $baseFrequency;

    $perfCtlMsr         = MSR::read($cpuId, self::IA32_PERF_CTL);
    $performanceControl = $perfCtlMsr->value;

    return new self($cpuId, $performanceState, $performanceControl, $frequency);
  }

}