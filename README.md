# Laravel Hashidable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mcris112/laravel-hashidable.svg?style=flat-square)](https://packagist.org/packages/mcris112/laravel-hashidable)
[![License](https://img.shields.io/packagist/l/mcris112/laravel-hashidable.svg?style=flat-square)](https://packagist.org/packages/mcris112/laravel-hashidable)

**Laravel Hashidable** provides a seamless way to use [Hashids](https://hashids.org/) in your Laravel models. It automatically handles encoding/decoding of IDs for routing and database lookups, keeping your internal IDs hidden from the public.

This package is an enhanced fork of the original `kayandra/hashidable`, featuring improved type safety, caching support, global helpers, and fluent query builder integration.

## ✨ Key Features

- 🛡️ **Automatic Route Model Binding**: Uses hashids in URLs instead of plain integers.
- 🚀 **Performance Caching**: Decoded hashids can be cached to improve performance.
- 🛠️ **Fluent Scopes**: Chainable methods like `whereHashid()`, `orWhereHashid()`, and `findByHashid()`.
- 🧬 **Relation Support**: Easily load relations when finding by hashid using `with()`.
- ✅ **Custom Validation**: Built-in `hashid_exists` rule for validating hashids in requests.
- 🌍 **Global Helpers**: Simple `hashid_encode()` and `hashid_decode()` functions.
- 🎨 **Customizable**: Per-model configuration for salts, lengths, and alphabets.

---

## 📥 Installation

```bash
composer require mcris112/laravel-hashidable
```

## ⚙️ Setup

Add the `Hashidable` trait to your Eloquent model:

```php
use Mcris112\LaravelHashidable\Hashidable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Hashidable;
}
```

---

## 🚀 Usage

### Basic Operations

```php
$user = User::find(1);

// Accessing the hashid
echo $user->hashid; // e.g., "3RwQaeoOR1E7qjYy"

// Finding by hashid
$user = User::findByHashid("3RwQaeoOR1E7qjYy");
$user = User::findByHashidOrFail("3RwQaeoOR1E7qjYy");

// Decoding manually via model
$id = User::hashIdDecode("3RwQaeoOR1E7qjYy"); // 1
```

### Advanced Querying & Relations

Thanks to the new fluent scopes, you can now load relationships while finding by hashid:

```php
// Find with relations (Fluent way)
$user = User::with('posts', 'profile')->findByHashid($hashid);

// Using whereHashid in complex queries
$user = User::whereHashid($hashid)
    ->where('active', true)
    ->with('orders')
    ->firstOrFail();

// Using orWhereHashid
$user = User::where('email', 'admin@example.com')
    ->orWhereHashid($hashid)
    ->first();
```

### Validation Rule

You can validate that a hashid exists in the database using the `hashid_exists` rule. This automatically decodes the hashid before checking the database.

```php
use Illuminate\Http\Request;

public function update(Request $request)
{
    $request->validate([
        'user_id' => 'required|hashid_exists:App\Models\User,id',
    ]);
}
```

The rule accepts two parameters:
1. The **Model class** or **Table name**.
2. The **Database column** (optional, defaults to the model's primary key or `id`).

### Global Helpers

Decode or encode hashids anywhere without needing a model instance:

```php
// Decode hashid for a specific model class
$id = hashid_decode(User::class, "3RwQaeoOR1E7qjYy");

// Encode an ID for a specific model
$hash = hashid_encode(User::class, 1);
```

---

## 🔗 Route Model Binding

This package automatically handles Route Model Binding. Instead of IDs, your routes will use hashids:

```php
// routes/web.php
Route::get('/users/{user}', [UserController::class, 'show']);

// In your controller
public function show(User $user) 
{
    return view('users.show', compact('user'));
}
```

Generating links automatically uses the hashid:
```php
$url = route('users.show', $user); // /users/3RwQaeoOR1E7qjYy
```

---

## ⚡ Performance Caching

If you find yourself decoding the same hashids frequently, you can enable caching in the config. This will store the decoded integer ID in your cache store.

```php
// config/hashidable.php
'cache' => [
    'enabled' => true,
    'ttl' => 86400, // 24 hours
],
```

---

## 🛠️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=hashidable.config
```

### Global Config (`config/hashidable.php`)

| Option | Description | Default |
|--------|-------------|---------|
| `salt` | Unique salt for hash generation | `env(HASHIABLE_SALT)` |
| `length` | Minimum length of generated hash | `16` |
| `charset` | Characters used in the hashid | `a-zA-Z0-9` |
| `prefix` | Optional prefix for hashids | `""` |
| `suffix` | Optional suffix for hashids | `""` |
| `separator`| Separator between prefix/suffix | `"-"` |

### Per-Model Configuration

Implement `HashidableConfigInterface` to customize settings for a specific model:

```php
use Mcris112\LaravelHashidable\HashidableConfigInterface;

class Post extends Model implements HashidableConfigInterface
{
    use Hashidable;

    public function hashidableConfig()
    {
        return [
            'length' => 10,
            'prefix' => 'post',
            'separator' => '_',
        ];
    }
}
```

---

## ❓ FAQ

**Q: Are hashes stored in the database?**  
A: No. Hashes are calculated dynamically based on your model's ID and salt.

**Q: What happens if I change my salt?**  
A: All existing hashids will change. It's recommended to set a permanent salt in your `.env` file (`HASHIABLE_SALT`).

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
