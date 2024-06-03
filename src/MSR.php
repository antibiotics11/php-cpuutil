<?php

namespace CpuUtils;
use InvalidArgumentException, RuntimeException;
use function sprintf;
use function is_readable;
use function dio_open, dio_seek, dio_read, dio_close;
use function unpack;
use const O_RDONLY;

class MSR {

  protected function __construct(
    /** @var int $cpuId cpu identifier */
    public readonly int $cpuId = 0,
    /** @var int $register register address (unit32_t) */
    public readonly int $register = 0x00000000,
    /** @var int $value value read from the register (uint64_t) */
    public readonly int $value = 0x0000000000000000
  ) {}

  /**
   * Read the value from a specific MSR register.
   *
   * @param int $cpuId    cpu identifier
   * @param int $register register address (uint32_t)
   * @return self
   * @throws InvalidArgumentException if the msr file does not exist or is not readable.
   * @throws RuntimeException         if there is a failure opening the file, seeking the register, or reading/unpacking data.
   */
  public static function read(int $cpuId, int $register): self {
    $msrFile = sprintf("/dev/cpu/%d/msr", $cpuId);
    if (!is_readable($msrFile)) {
      throw new InvalidArgumentException("MSR file is not readable.");
    }

    $fileDescriptor = dio_open($msrFile, O_RDONLY);
    if ($fileDescriptor === false) {
      throw new RuntimeException("Failed to open MSR file.");
    }

    if (@dio_seek($fileDescriptor, $register) !== $register) {
      throw new RuntimeException("Failed to seek to the correct register.");
    }

    $data = @dio_read($fileDescriptor, 8);
    if (false !== $unpacked = @unpack("P", $data)) {
      $value = $unpacked[1] ?? 0x0000000000000000;

      dio_close($fileDescriptor);
      return new self($cpuId, $register, $value);
    }

    dio_close($fileDescriptor);
    throw new RuntimeException("Failed to read or unpack data from MSR file.");
  }

}