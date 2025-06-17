# AJAX Controllers Guide

### Controller Inheritance

Always extend `AbstractMollieController` instead of `ModuleFrontController` for AJAX requests:

```php
// Correct way
class YourAjaxController extends AbstractMollieController
{
    // Your controller code
}

// Incorrect way
class YourAjaxController extends ModuleFrontController
{
    // Will return HTTP 500 instead of 200
}
```

### Why?

- Default PrestaShop controllers return HTTP 500 for `ajaxRender()` responses
- `AbstractMollieController` ensures proper HTTP 200 status
- Provides built-in error handling and logging
- Formats JSON responses correctly

### Example

```php
class MollieCustomAjaxModuleFrontController extends AbstractMollieController
{
    public function postProcess()
    {
        try {
            $result = ['success' => true];
            $this->ajaxRender(json_encode($result));
        } catch (\Throwable $e) {
            $this->ajaxRender('Error processing request');
        }
    }
}
```

## Best Practices

1. Always use `ajaxRender()` for sending responses
2. Properly handle exceptions
3. Include appropriate error logging
4. Set proper content types
5. Follow the Mollie error handling patterns

Remember: Using `AbstractMollieController` is crucial for maintaining consistent and reliable AJAX functionality across the module.
