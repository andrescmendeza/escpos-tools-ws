# ESCPOS Commands Coverage Comparison

| Category          | Command/Function                | Implemented | Notes        |
|-------------------|-------------------------------|-------------|--------------|
| Text              | Line feed (LF)                | Yes         |              |
| Text              | Tabulation (HT)               | Yes         |              |
| Text              | Carriage return (CR)          | Yes         |              |
| Text              | Font selection                | Yes         |              |
| Text              | Character size                | Yes         |              |
| Text              | Bold, underline, emphasis     | Yes         |              |
| Text              | Justification                 | Yes         |              |
| Text              | Inverted, double, etc. modes  | Yes         |              |
| Text              | Character sets                | Partial     | Missing manufacturer-specific variants |
| Text              | Vertical/bidirectional text   | No          | Missing      |
| Graphics          | Bit image, raster, graphics   | Yes         |              |
| Graphics          | Advanced graphics modes       | Partial     | Some subcommands empty |
| Graphics          | Image storage variants        | Partial     | Some variants missing |
| Barcodes          | 1D (EAN, CODE39, etc.)        | Yes         |              |
| Barcodes          | 2D (QR, PDF417, MaxiCode)     | No          | Missing      |
| Other             | Paper cut                     | Yes         |              |
| Other             | Pulse, sensors, panel         | Yes         |              |
| Other             | Initialization, cancel        | Yes         |              |
| Other             | Unknown commands              | Yes         |              |

## Priority Missing Commands
- 2D barcodes: QR, PDF417, MaxiCode, DataMatrix.
- Vertical and bidirectional text modes.
- Extended/manufacturer-specific character sets.
- Advanced graphics subcommands and image storage variants.

## Suggested Next Steps
1. Implement classes for 2D barcode commands (e.g., `PrintQRCodeCmd`, `PrintPDF417Cmd`).
2. Add support for vertical and bidirectional text modes.
3. Complete advanced graphics subcommands and image storage variants.
4. Review and expand character set support.
5. Add unit tests and documentation for each new command.
