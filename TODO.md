# TODO for RPCI Report PDF Print Modifications

- [x] Edit resources/views/client/report/rpci/pdf.blade.php to add @media print CSS styles to remove body margin and padding for print media, ensuring only header, accountability section, and table are visible in PDF output.
- [x] Edit resources/views/client/report/rpci/index.blade.php to hide unnecessary elements like Monitoring Management System, Agricultural Training Institute - Regional Training Center 1, user profile, and export icons in print view.
- [x] Edit resources/views/client/report/rpci/index.blade.php to show the accountability info (applied header) in print view.

# TODO for RPC-PPE Report Print Modifications

- [x] Edit resources/views/client/report/rpc-ppe/index.blade.php to hide the report-info and accountability-info sections and show only the applied-header in print view.
- [x] Edit resources/views/client/report/rpc-ppe/pdf.blade.php to show the applied header with underscores instead of dynamic data.
- [x] Apply the same print CSS modifications to all remaining reports (RPC-PPE, RSMI, PPES).
