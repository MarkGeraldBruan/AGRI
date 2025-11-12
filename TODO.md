# TODO: Fix "AS OF" field in all reports

## Controllers
- [ ] Update RpcPpeController.php: Change 'as_of' to use request('as_of') formatted or blank
- [ ] Update PpesController.php: Change 'as_of' to use request('as_of') formatted or blank
- [ ] Update RpciController.php: Change 'as_of' to use request('as_of') formatted or blank
- [ ] Update RsmiController.php: Change 'as_of' to use request('as_of') formatted or blank

## Views (Index)
- [ ] Update rpc-ppe/index.blade.php: Remove default value from "As of" input
- [ ] Update ppes/index.blade.php: Remove default value from "As of" input
- [ ] Update rpci/index.blade.php: Remove default value from "As of" input
- [ ] Update rsmi/index.blade.php: Remove default value from "As of" input

## Views (PDF)
- [ ] Update rpc-ppe/pdf.blade.php: Show '______' if blank
- [ ] Update ppes/pdf.blade.php: Show '______' if blank
- [ ] Update rpci/pdf.blade.php: Show '______' if blank (if exists)
- [ ] Update rsmi/pdf.blade.php: Show '______' if blank (if exists)

## Exports
- [x] Update RpcPpeExport.php: Use formatted date or '______'
- [x] Update PpesExport.php: Use formatted date or '______'
- [x] Update RpciExport.php: Use formatted date or '______'
- [x] Update RsmiExport.php: Use formatted date or '______'
