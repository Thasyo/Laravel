# Models no Laravel - Estudo Avançado

## 1. Introdução aos Models no Laravel

### O que são Models no Laravel

Os Models no Laravel são classes que representam tabelas do banco de dados e servem como a camada de interação entre sua aplicação e os dados. Eles implementam o padrão **Active Record** através do **Eloquent ORM** (Object-Relational Mapping), proporcionando uma interface elegante e expressiva para trabalhar com dados[^1][^2].

O Eloquent ORM é o mapeador objeto-relacional incluído no Laravel que fornece uma implementação simples e bonita do padrão ActiveRecord para trabalhar com seu banco de dados. Cada tabela do banco de dados possui um "Model" correspondente que é usado para interagir com essa tabela[^1][^2].

### Convenções de Nomenclatura

O Laravel segue convenções específicas para nomeação e estrutura dos Models[^3]:

- **Nome da classe**: Singular, em PascalCase (ex: `User`, `BlogPost`)
- **Nome da tabela**: Plural, em snake_case (ex: `users`, `blog_posts`)
- **Chave primária**: Por padrão, assume-se que seja `id`
- **Timestamps**: Colunas `created_at` e `updated_at` são automaticamente gerenciadas


### Criação e Configuração Básica

Para criar um novo Model, utilize o comando Artisan[^4]:

```bash
php artisan make:model User
```

Para criar um Model com migration simultaneamente:

```bash
php art
# ou
php artisan make:model User -m
```

Estrutura básica de um Model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Configurações do model
}
```


## 2. Configuração e Estrutura de Models

### Propriedades Fundamentais

#### \$table - Especificando a Tabela

Por padrão, o Laravel assume que a tabela tem o nome plural do model. Para especificar uma tabela customizada[^1]:

```php
class User extends Model
{
    protected $table = 'my_users';
}
```


#### \$primaryKey - Definindo Chave Primária

```php
class User extends Model
{
    protected $primaryKey = 'user_id';
    
    // Se a chave primária não for um inteiro auto-incrementável
    public $incrementing = false;
    protected $keyType = 'string';
}
```


#### \$timestamps - Controle de Timestamps

```php
class User extends Model
{
    // Desabilitar timestamps automáticos
    public $timestamps = false;
    
    // Ou customizar os nomes das colunas
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}
```


### Atribuição em Massa (\$fillable e \$guarded)

A atribuição em massa é uma funcionalidade que permite definir múltiplos atributos de um model simultaneamente. Para segurança, o Laravel protege contra vulnerabilidades de mass assignment[^5][^6][^7].

#### \$fillable - Lista Branca

Define quais atributos podem ser atribuídos em massa[^5][^6]:

```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}

// Uso
User::create([
    'name' => 'João Silva',
    'email' => 'joao@email.com',
    'password' => bcrypt('senha123')
]);
```


#### \$guarded - Lista Negra

Define quais atributos NÃO podem ser atribuídos em massa[^6][^7]:

```php
class User extends Model
{
    protected $guarded = [
        'id',
        'is_admin',
        'created_at',
        'updated_at'
    ];
}

// Para permitir todos os campos
protected $guarded = [];
```

**Exemplo de Vulnerabilidade de Mass Assignment**[^8][^9]:

```php
// PERIGOSO - permite modificar qualquer campo
Route::post('/profile', function (Request $request) {
    $request->user()->update($request->all());
});

// SEGURO - usando validação
Route::post('/profile', function (Request $request) {
    $request->user()->update($request->validated());
});
```


### Campos Ocultos e Visíveis (\$hidden, \$visible)

#### \$hidden - Ocultando Atributos

Define quais atributos devem ser ocultados na serialização JSON[^10][^11]:

```php
class User extends Model
{
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```


#### \$visible - Mostrando Apenas Atributos Específicos

```php
class User extends Model
{
    protected $visible = [
        'name',
        'email',
    ];
}
```


### Casting de Atributos (\$casts)

O casting permite que você converta automaticamente atributos para tipos específicos quando acessados[^12][^13]:

```php
class User extends Model
{
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'settings' => 'array',
        'balance' => 'decimal:2',
        'birthdate' => 'date:Y-m-d',
    ];
}

// Uso
$user = User::find(1);
$user->is_admin; // retorna boolean true/false
$user->settings; // retorna array
$user->email_verified_at; // retorna instância Carbon
```


#### Casting Personalizado

Você pode criar casts personalizados:

```bash
php artisan make:cast Json
```

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Json implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return json_decode($value, true);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return json_encode($value);
    }
}
```


### Conexões de Banco de Dados (\$connection)

Para usar uma conexão específica do banco de dados[^14][^15]:

```php
class User extends Model
{
    protected $connection = 'mysql2';
}
```

Configuração no `config/database.php`:

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'primary_db'),
        // ...
    ],
    
    'mysql2' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST_SECONDARY', '127.0.0.1'),
        'database' => env('DB_DATABASE_SECONDARY', 'secondary_db'),
        // ...
    ],
],
```


## 3. Relacionamentos entre Models

### One to One (Um para Um)

#### hasOne

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Uso
$user = User::find(1);
$profile = $user->profile;
```


#### belongsTo

```php
class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
```


### One to Many (Um para Muitos)

#### hasMany

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Uso
$user = User::find(1);
foreach ($user->posts as $post) {
    echo $post->title;
}
```


### Many to Many (Muitos para Muitos)

```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

// Com tabela pivot personalizada
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withPivot('assigned_at', 'assigned_by')
                    ->withTimestamps();
    }
}

// Uso
$user = User::find(1);
foreach ($user->roles as $role) {
    echo $role->name;
    echo $role->pivot->assigned_at; // acessando dados da pivot
}
```


### Has Many Through

```php
class Country extends Model
{
    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}

// Country -> User -> Post
// Um país tem muitos posts através de usuários
```


### Relacionamentos Polimórficos

#### One to One Polimórfico

```php
class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class User extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}

class Product extends Model
{
    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
```


#### One to Many Polimórfico

```php
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```


### Eager Loading e Lazy Loading

#### Eager Loading - Carregamento Antecipado

Previne o problema N+1:

```php
// Problema N+1 - executa muitas queries
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // Query para cada usuário
}

// Solução com Eager Loading
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // Apenas 2 queries no total
}

// Eager Loading aninhado
$users = User::with('posts.comments')->get();

// Eager Loading condicional
$users = User::with(['posts' => function ($query) {
    $query->where('published', true);
}])->get();
```


#### Lazy Eager Loading

```php
$users = User::all();

// Carrega relacionamentos após a consulta inicial
if ($someCondition) {
    $users->load('posts', 'comments');
}
```


## 4. Mutators, Accessors e Casting

### Definindo Accessors

Accessors transformam valores de atributos quando são acessados[^12][^13]:

```php
class User extends Model
{
    // Laravel 9+
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }
    
    // Versões anteriores
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }
    
    // Accessor computado
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }
}

// Uso
$user = User::find(1);
echo $user->first_name; // João (capitalizado automaticamente)
echo $user->full_name;  // João Silva
```


### Definindo Mutators

Mutators modificam valores de atributos quando são definidos[^13][^16]:

```php
class User extends Model
{
    // Laravel 9+
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }
    
    // Versões anteriores
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtolower($value);
    }
    
    // Mutator para hash de senha
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => bcrypt($value),
        );
    }
}

// Uso
$user = new User();
$user->first_name = 'JOÃO'; // Armazenado como 'joão'
$user->password = 'senha123'; // Armazenado como hash
```


### Diferenças entre Mutators/Accessors e Casting

**Casting** é ideal para conversões simples e padronizadas, enquanto **Mutators/Accessors** oferecem mais flexibilidade para lógica personalizada[^17][^12]:

```php
class Product extends Model
{
    // Casting simples
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Accessor personalizado para formatação
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'R$ ' . number_format($this->price, 2, ',', '.'),
        );
    }
    
    // Mutator para validação/transformação
    protected function price(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => max(0, (float) $value),
        );
    }
}
```


## 5. Query Scopes

Query Scopes permitem definir restrições de consulta reutilizáveis[^18][^19].

### Local Scopes

```php
class Post extends Model
{
    // Scope tradicional
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
    
    // Scope com parâmetros
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    // Laravel 12+ - usando atributo #[Scope]
    #[Scope]
    protected function popular(Builder $query): void
    {
        $query->where('votes', '>', 100);
    }
}

// Uso dos scopes
$posts = Post::published()->get();
$articles = Post::published()->ofType('article')->get();
$popularPosts = Post::popular()->get();

// Combinando scopes
$posts = Post::published()
             ->ofType('article')
             ->orderBy('created_at', 'desc')
             ->take(10)
             ->get();
```


### Global Scopes

Global Scopes são aplicados automaticamente a todas as consultas do model[^18]:

#### Criando um Global Scope

```bash
php artisan make:scope ActiveScope
```

```php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('active', true);
    }
}
```


#### Aplicando Global Scope

```php
class User extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);
    }
    
    // Ou usando scope anônimo
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', true);
        });
    }
}

// Ignorando global scopes
$users = User::withoutGlobalScope(ActiveScope::class)->get();
$allUsers = User::withoutGlobalScopes()->get();
```


### Exemplo Prático de Scopes

```php
class User extends Model
{
    // Scope para usuários ativos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    // Scope para busca por nome ou email
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
        });
    }
    
    // Scope para usuários com papel específico
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        });
    }
}

// Uso combinado
$admins = User::active()
              ->search('joão')
              ->withRole('admin')
              ->orderBy('name')
              ->paginate(15);
```


## 6. Eventos de Models (Model Events)

Os Model Events permitem executar código automaticamente em pontos específicos do ciclo de vida do model[^20][^21][^22].

### Tipos de Eventos Disponíveis

- **retrieved**: quando um model é recuperado do banco
- **creating**: antes de criar um novo registro
- **created**: após criar um novo registro
- **updating**: antes de atualizar um registro
- **updated**: após atualizar um registro
- **saving**: antes de salvar (criar ou atualizar)
- **saved**: após salvar (criar ou atualizar)
- **deleting**: antes de deletar
- **deleted**: após deletar
- **restoring**: antes de restaurar (soft delete)
- **restored**: após restaurar (soft delete)


### Implementação usando Closures

```php
class User extends Model
{
    protected static function booted(): void
    {
        // Evento de criação
        static::creating(function (User $user) {
            $user->uuid = Str::uuid();
        });
        
        // Evento de atualização
        static::updating(function (User $user) {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        });
        
        // Evento de salvamento
        static::saving(function (User $user) {
            $user->slug = Str::slug($user->name);
        });
        
        // Evento de exclusão
        static::deleting(function (User $user) {
            // Verificar se pode deletar
            if ($user->posts()->count() > 0) {
                throw new \Exception('Cannot delete user with posts');
            }
        });
    }
}
```


### Model Observers

Para lógica mais complexa, use Observers[^20][^21]:

```bash
php artisan make:observer UserObserver --model=User
```

```php
<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->uuid = Str::uuid();
    }
    
    public function created(User $user): void
    {
        // Enviar email de boas-vindas
        Mail::to($user)->send(new WelcomeEmail($user));
    }
    
    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }
    
    public function deleting(User $user): void
    {
        // Deletar dados relacionados
        $user->posts()->delete();
        $user->comments()->delete();
    }
}
```

Registrando o Observer:

```php
// No AppServiceProvider
public function boot(): void
{
    User::observe(UserObserver::class);
}

// Ou usando atributo (Laravel 10+)
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(UserObserver::class)]
class User extends Model
{
    // ...
}
```


### Trait \$dispatchesEvents

```php
class User extends Model
{
    protected $dispatchesEvents = [
        'created' => UserCreated::class,
        'updated' => UserUpdated::class,
        'deleted' => UserDeleted::class,
    ];
}

// Event class
class UserCreated
{
    public function __construct(public User $user)
    {
        //
    }
}

// Event Listener
class SendWelcomeEmail
{
    public function handle(UserCreated $event): void
    {
        Mail::to($event->user)->send(new WelcomeEmail($event->user));
    }
}
```


## 7. Collections Personalizadas

### Criando Collections Customizadas

```php
<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class PostCollection extends Collection
{
    public function published()
    {
        return $this->filter(function ($post) {
            return $post->published;
        });
    }
    
    public function byAuthor($authorId)
    {
        return $this->filter(function ($post) use ($authorId) {
            return $post->author_id === $authorId;
        });
    }
    
    public function summarize()
    {
        return $this->map(function ($post) {
            return [
                'title' => $post->title,
                'excerpt' => Str::limit($post->content, 100),
                'author' => $post->author->name,
                'published_at' => $post->published_at->format('d/m/Y'),
            ];
        });
    }
    
    public function totalReadingTime()
    {
        return $this->sum(function ($post) {
            return str_word_count($post->content) / 200; // 200 palavras por minuto
        });
    }
}
```


### Integrando com Models

```php
class Post extends Model
{
    public function newCollection(array $models = [])
    {
        return new PostCollection($models);
    }
}

// Uso
$posts = Post::all(); // Retorna PostCollection
$publishedPosts = $posts->published();
$summary = $posts->summarize();
$readingTime = $posts->totalReadingTime();

// Em relacionamentos
$user = User::with('posts')->first();
$userPosts = $user->posts; // Também retorna PostCollection
```


### Exemplo Avançado

```php
class OrderCollection extends Collection
{
    public function total()
    {
        return $this->sum('total_amount');
    }
    
    public function byStatus($status)
    {
        return $this->filter(fn($order) => $order->status === $status);
    }
    
    public function pending()
    {
        return $this->byStatus('pending');
    }
    
    public function completed()
    {
        return $this->byStatus('completed');
    }
    
    public function averageOrderValue()
    {
        return $this->isEmpty() ? 0 : $this->avg('total_amount');
    }
    
    public function groupedByMonth()
    {
        return $this->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        });
    }
}

// Uso
$orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();

$totalRevenue = $orders->total();
$pendingOrders = $orders->pending();
$monthlyOrders = $orders->groupedByMonth();
$avgOrderValue = $orders->averageOrderValue();
```


## 8. Soft Deletes

Soft Deletes permitem "deletar" registros sem removê-los fisicamente do banco de dados[^23][^24][^25].

### Implementação de Soft Deletes

#### Migration

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->softDeletes(); // Adiciona coluna deleted_at
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
}
```


#### Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at']; // Laravel < 7
    
    // Laravel 7+
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
```


### Operações com Soft Deletes

```php
// Soft delete
$user = User::find(1);
$user->delete(); // Define deleted_at com timestamp atual

// Verificar se foi soft deleted
if ($user->trashed()) {
    echo 'Usuário foi deletado';
}

// Incluir registros soft deleted
$users = User::withTrashed()->get();

// Apenas registros soft deleted
$deletedUsers = User::onlyTrashed()->get();

// Restaurar registro
$user = User::withTrashed()->find(1);
$user->restore(); // Define deleted_at como null

// Restaurar múltiplos registros
User::onlyTrashed()
    ->where('deleted_at', '<', now()->subDays(30))
    ->restore();

// Force delete (deletar permanentemente)
$user->forceDelete();

// Force delete com query
User::onlyTrashed()
    ->where('deleted_at', '<', now()->subYear())
    ->forceDelete();
```


### Soft Deletes em Relacionamentos

```php
class User extends Model
{
    use SoftDeletes;
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    use SoftDeletes;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Incluir posts soft deleted do usuário
$user = User::with(['posts' => function ($query) {
    $query->withTrashed();
}])->find(1);

// Relacionamento que inclui soft deleted automaticamente
class User extends Model
{
    use SoftDeletes;
    
    public function allPosts()
    {
        return $this->hasMany(Post::class)->withTrashed();
    }
}
```


### Limpeza Automática de Soft Deletes

```php
use Illuminate\Database\Eloquent\Prunable;

class User extends Model
{
    use SoftDeletes, Prunable;
    
    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subMonth());
    }
    
    protected function pruning()
    {
        // Executar antes da limpeza
        $this->posts()->forceDelete();
    }
}
```

Agendar limpeza:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('model:prune')->daily();
}
```


### Eventos com Soft Deletes

```php
class User extends Model
{
    use SoftDeletes;
    
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Executado no soft delete
            Log::info("User {$user->id} was soft deleted");
        });
        
        static::forceDeleting(function (User $user) {
            // Executado no force delete
            Log::info("User {$user->id} was permanently deleted");
        });
        
        static::restoring(function (User $user) {
            // Executado na restauração
            Log::info("User {$user->id} is being restored");
        });
        
        static::restored(function (User $user) {
            // Executado após restauração
            Log::info("User {$user->id} was restored");
        });
    }
}
```


## 9. Model Factories e Seeders

Model Factories facilitam a criação de dados fake para testes e desenvolvimento[^26][^27][^28].

### Criação de Factories

```bash
php artisan make:factory UserFactory --model=User
```

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;
    
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
    
    // State para email não verificado
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    
    // State para admin
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }
    
    // State para usuário inativo
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
```


### States em Factories

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(5, true),
            'status' => 'draft',
            'published_at' => null,
            'author_id' => User::factory(),
        ];
    }
    
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
    
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
    
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }
}
```


### Relacionamentos em Factories

```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(5, true),
            'author_id' => User::factory(), // Cria usuário automaticamente
        ];
    }
    
    // Factory com usuário específico
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
        ]);
    }
}

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(),
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
        ];
    }
}

// Uso
$user = User::factory()->create();
$posts = Post::factory()->count(5)->forUser($user)->create();

// Criando com relacionamentos
$post = Post::factory()
    ->has(Comment::factory()->count(3))
    ->create();

// Ou usando relacionamento definido
$post = Post::factory()
    ->hasComments(3)
    ->create();
```


### Factory para Relacionamentos Many-to-Many

```php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ];
    }
    
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            // Anexar roles aleatórios
            $roles = Role::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->roles()->attach($roles);
        });
    }
}

// Ou usando hasAttached
$user = User::factory()
    ->hasAttached(Role::factory()->count(2))
    ->create();

// Com dados pivot
$user = User::factory()
    ->hasAttached(
        Role::factory()->count(2),
        ['assigned_at' => now()]
    )
    ->create();
```


### Integração com Seeders

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criar admin
        $admin = User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);
        
        // Criar usuários normais
        $users = User::factory()->count(50)->create();
        
        // Criar posts para cada usuário
        $users->each(function (User $user) {
            Post::factory()
                ->count(rand(1, 5))
                ->forUser($user)
                ->create()
                ->each(function (Post $post) use ($user) {
                    // Adicionar comentários aos posts
                    Comment::factory()
                        ->count(rand(0, 10))
                        ->create([
                            'post_id' => $post->id,
                            'user_id' => $user->id,
                        ]);
                });
        });
    }
}
```


### Uso em Testes

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/posts', [
                'title' => 'Test Post',
                'content' => 'This is a test post content.',
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author_id' => $user->id,
        ]);
    }
    
    public function test_only_published_posts_are_visible(): void
    {
        // Criar posts com diferentes status
        Post::factory()->count(3)->published()->create();
        Post::factory()->count(2)->draft()->create();
        
        $response = $this->get('/posts');
        
        $response->assertOk();
        // Verificar se apenas posts publicados são exibidos
        $this->assertEquals(3, Post::published()->count());
    }
}
```


## 10. Serialização

A serialização permite converter Models em arrays ou JSON para APIs[^10][^11][^29].

### toArray() e toJson()

```php
$user = User::find(1);

// Para array
$array = $user->toArray();

// Para JSON
$json = $user->toJson();

// Com pretty print
$json = $user->toJson(JSON_PRETTY_PRINT);

// Casting automático para string
return (string) $user; // Retorna JSON

// Em routes
Route::get('/users', function () {
    return User::all(); // Automaticamente convertido para JSON
});
```


### Controle de Campos Serializados

#### Hidden - Ocultando Atributos

```php
class User extends Model
{
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];
}

// Ocultando dinamicamente
$users = User::all()->makeHidden(['email', 'phone']);
```


#### Visible - Mostrando Apenas Atributos Específicos

```php
class User extends Model
{
    protected $visible = [
        'id',
        'name',
        'email',
    ];
}

// Tornando visível dinamicamente
$users = User::all()->makeVisible(['phone']);
```


### Appends em Serialização

```php
class User extends Model
{
    protected $appends = [
        'full_name',
        'avatar_url',
    ];
    
    // Accessor que será incluído na serialização
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }
    
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => 'https://gravatar.com/avatar/' . md5($this->email),
        );
    }
}

// Adicionando appends dinamicamente
$users = User::all()->append(['is_online', 'last_login_human']);

// Resultado JSON incluirá full_name e avatar_url
```


### Controle Condicional de Serialização

```php
class Post extends Model
{
    protected $hidden = ['user_id'];
    
    protected $appends = ['can_edit'];
    
    public function toArray()
    {
        $array = parent::toArray();
        
        // Adicionar campos condicionalmente
        if (auth()->check() && auth()->id() === $this->user_id) {
            $array['edit_url'] = route('posts.edit', $this);
        }
        
        // Remover campos sensíveis para usuários não autenticados
        if (!auth()->check()) {
            unset($array['internal_notes']);
        }
        
        return $array;
    }
    
    protected function canEdit(): Attribute
    {
        return Attribute::make(
            get: fn () => auth()->check() && 
                         (auth()->id() === $this->user_id || auth()->user()->is_admin),
        );
    }
}
```


### Serialização de Relacionamentos

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Incluir relacionamentos na serialização
$user = User::with('posts')->find(1);
$array = $user->toArray(); // Inclui posts

// Controlar serialização de relacionamentos
class User extends Model
{
    public function toArray()
    {
        $array = parent::toArray();
        
        // Incluir apenas posts publicados
        if ($this->relationLoaded('posts')) {
            $array['posts'] = $this->posts->filter(fn($post) => $post->published)->values();
        }
        
        return $array;
    }
}
```


### Recursos de API (API Resources)

Para controle mais granular, use API Resources:

```bash
php artisan make:resource UserResource
```

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at->toISOString(),
            
            // Incluir condicionalmente
            'is_admin' => $this->when($this->is_admin, true),
            'posts_count' => $this->when(
                $request->include === 'posts_count',
                $this->posts()->count()
            ),
            
            // Relacionamentos
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ];
    }
}

// Uso
Route::get('/users/{user}', function (User $user) {
    return new UserResource($user->load('posts'));
});
```


## 11. Múltiplas Conexões de Banco

Laravel suporta múltiplas conexões de banco de dados simultaneamente[^14][^15][^30].

### Configuração de Múltiplas Conexões

#### config/database.php

```php
'connections' => [
    'mysql_primary' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'primary_db'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    
    'mysql_analytics' => [
        'driver' => 'mysql',
        'host' => env('DB_ANALYTICS_HOST', '127.0.0.1'),
        'port' => env('DB_ANALYTICS_PORT', '3306'),
        'database' => env('DB_ANALYTICS_DATABASE', 'analytics_db'),
        'username' => env('DB_ANALYTICS_USERNAME', 'forge'),
        'password' => env('DB_ANALYTICS_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
    
    'postgresql_reports' => [
        'driver' => 'pgsql',
        'host' => env('DB_REPORTS_HOST', '127.0.0.1'),
        'port' => env('DB_REPORTS_PORT', '5432'),
        'database' => env('DB_REPORTS_DATABASE', 'reports_db'),
        'username' => env('DB_REPORTS_USERNAME', 'forge'),
        'password' => env('DB_REPORTS_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
    ],
],
```


### Uso de Conexões Específicas em Models

```php
class User extends Model
{
    protected $connection = 'mysql_primary';
}

class AnalyticsEvent extends Model
{
    protected $connection = 'mysql_analytics';
    protected $table = 'events';
}

class Report extends Model
{
    protected $connection = 'postgresql_reports';
}
```


### Alterando Conexão Dinamicamente

```php
// Em queries
$users = User::on('mysql_primary')->get();
$events = AnalyticsEvent::on('mysql_analytics')->where('type', 'click')->get();

// Usando DB facade
$users = DB::connection('mysql_primary')->table('users')->get();
$reports = DB::connection('postgresql_reports')->table('reports')->get();

// Alterando conexão de um model
$user = new User();
$user->setConnection('mysql_analytics');
$user->save();
```


### Transações em Múltiplas Conexões

```php
use Illuminate\Support\Facades\DB;

// Transação em conexão específica
DB::connection('mysql_primary')->transaction(function () {
    User::create([...]);
    Profile::create([...]);
});

// Transações em múltiplas conexões
DB::connection('mysql_primary')->beginTransaction();
DB::connection('mysql_analytics')->beginTransaction();

try {
    // Operações na conexão primária
    User::create([...]);
    
    // Operações na conexão de analytics
    AnalyticsEvent::on('mysql_analytics')->create([...]);
    
    DB::connection('mysql_primary')->commit();
    DB::connection('mysql_analytics')->commit();
} catch (\Exception $e) {
    DB::connection('mysql_primary')->rollback();
    DB::connection('mysql_analytics')->rollback();
    throw $e;
}
```


### Relacionamentos Entre Conexões

```php
class User extends Model
{
    protected $connection = 'mysql_primary';
    
    public function analyticsEvents()
    {
        // Relacionamento através de conexões diferentes
        return $this->hasMany(AnalyticsEvent::class, 'user_id')
                    ->on('mysql_analytics');
    }
}

class AnalyticsEvent extends Model
{
    protected $connection = 'mysql_analytics';
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')
                    ->on('mysql_primary');
    }
}

// Uso
$user = User::with('analyticsEvents')->find(1);
```


### Migration com Múltiplas Conexões

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsEventsTable extends Migration
{
    protected $connection = 'mysql_analytics';
    
    public function up()
    {
        Schema::connection('mysql_analytics')->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('event_type');
            $table->json('data');
            $table->timestamps();
            
            $table->index(['user_id', 'event_type']);
        });
    }
    
    public function down()
    {
        Schema::connection('mysql_analytics')->dropIfExists('events');
    }
}
```


### Exemplo Prático: Multi-Tenant

```php
class TenantAwareModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Definir conexão baseada no tenant atual
        if ($tenant = app('current_tenant')) {
            $this->setConnection("tenant_{$tenant->id}");
        }
    }
}

class User extends TenantAwareModel
{
    // Este model automaticamente usará a conexão do tenant atual
}

// Middleware para definir tenant
class SetTenantConnection
{
    public function handle($request, Closure $next)
    {
        $tenant = Tenant::where('domain', $request->getHost())->first();
        
        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            
            // Configurar conexão do tenant dinamicamente
            config([
                "database.connections.tenant_{$tenant->id}" => [
                    'driver' => 'mysql',
                    'host' => $tenant->db_host,
                    'database' => $tenant->db_name,
                    'username' => $tenant->db_username,
                    'password' => $tenant->db_password,
                    // ...
                ]
            ]);
        }
        
        return $next($request);
    }
}
```


## 12. Técnicas Avançadas e Otimização

### Performance e Otimização de Queries

#### Eager Loading Inteligente

```php
// Problema N+1
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Query para cada post
}

// Solução com Eager Loading
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // Apenas 2 queries
}

// Eager Loading condicional
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', true)
          ->orderBy('created_at', 'desc')
          ->limit(5);
}])->get();

// Eager Loading de relacionamentos específicos
$posts = Post::with('author:id,name,email')->get();
```


#### Lazy Eager Loading

```php
$posts = Post::all();

// Carregar relacionamentos depois se necessário
if ($request->include_comments) {
    $posts->load('comments.author');
}
```


#### Otimização com Select

```php
// Selecionar apenas campos necessários
$users = User::select(['id', 'name', 'email'])->get();

// Com relacionamentos
$posts = Post::with(['author' => function ($query) {
    $query->select(['id', 'name']);
}])->select(['id', 'title', 'author_id'])->get();
```


### Chunking para Grandes Datasets

```php
// Processar registros em lotes para evitar esgotamento de memória
User::chunk(100, function ($users) {
    foreach ($users as $user) {
        // Processar cada usuário
        $user->sendNewsletter();
    }
});

// Chunk com cursor (melhor performance)
User::lazy()->each(function ($user) {
    $user->sendNewsletter();
});

// Chunk by ID (mais eficiente para tabelas grandes)
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        $user->update(['last_processed' => now()]);
    }
});
```


### Custom Query Builders

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class PostQueryBuilder extends Builder
{
    public function published()
    {
        return $this->where('published', true)
                    ->where('published_at', '<=', now());
    }
    
    public function byAuthor($authorId)
    {
        return $this->where('author_id', $authorId);
    }
    
    public function popular($threshold = 100)
    {
        return $this->where('views', '>=', $threshold);
    }
    
    public function recent($days = 30)
    {
        return $this->where('created_at', '>=', now()->subDays($days));
    }
    
    public function search($term)
    {
        return $this->where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
        });
    }
}

class Post extends Model
{
    public function newEloquentBuilder($query)
    {
        return new PostQueryBuilder($query);
    }
}

// Uso
$posts = Post::published()
             ->popular()
             ->recent(7)
             ->search('Laravel')
             ->orderBy('created_at', 'desc')
             ->paginate(15);
```


### Índices e Otimização de Database

```php
// Migration com índices otimizados
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->boolean('published')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->unsignedBigInteger('author_id');
        $table->unsignedInteger('views')->default(0);
        $table->timestamps();
        
        // Índices para otimização
        $table->index(['published', 'published_at']); // Para posts publicados
        $table->index(['author_id']); // Para posts por autor
        $table->index(['views']); // Para posts populares
        $table->index(['created_at']); // Para ordenação por data
        
        // Índice composto para consultas complexas
        $table->index(['published', 'author_id', 'created_at']);
        
        // Índice full-text para busca
        $table->fullText(['title', 'content']);
    });
}
```


### Cache de Queries

```php
class Post extends Model
{
    public function scopePopular($query)
    {
        return $query->where('views', '>=', 100)
                     ->remember(60); // Cache por 60 minutos
    }
    
    // Método com cache personalizado
    public static function getPopularPosts()
    {
        return Cache::remember('popular_posts', 3600, function () {
            return static::where('views', '>=', 100)
                         ->orderBy('views', 'desc')
                         ->limit(10)
                         ->get();
        });
    }
    
    // Invalidar cache quando model for atualizado
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('popular_posts');
        });
        
        static::deleted(function () {
            Cache::forget('popular_posts');
        });
    }
}
```


### Segurança em Mass Assignment

#### Proteção Básica

```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    // Campos que nunca devem ser preenchidos em massa
    protected $guarded = [
        'id',
        'is_admin',
        'email_verified_at',
    ];
}
```


#### Validação Avançada

```php
class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        // Usar dados validados ao invés de $request->all()
        $user = User::create($request->validated());
        
        return response()->json($user, 201);
    }
    
    public function update(UpdateUserRequest $request, User $user)
    {
        // Controle fino sobre quais campos podem ser atualizados
        $allowedFields = ['name', 'email'];
        $data = $request->only($allowedFields);
        
        $user->update($data);
        
        return response()->json($user);
    }
}

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```


#### Proteção Dinâmica

```php
class User extends Model
{
    protected $fillable = ['*']; // Permitir todos inicialmente
    
    public function fill(array $attributes)
    {
        // Filtrar atributos baseado no contexto
        $allowedAttributes = $this->getAllowedAttributes();
        $filteredAttributes = array_intersect_key($attributes, array_flip($allowedAttributes));
        
        return parent::fill($filteredAttributes);
    }
    
    protected function getAllowedAttributes(): array
    {
        $user = auth()->user();
        
        $allowed = ['name', 'email'];
        
        // Admins podem modificar campos adicionais
        if ($user && $user->is_admin) {
            $allowed = array_merge($allowed, ['is_active', 'role']);
        }
        
        // Usuários podem modificar apenas seus próprios dados
        if ($user && $user->id === $this->id) {
            $allowed = array_merge($allowed, ['phone', 'address']);
        }
        
        return $allowed;
    }
}
```


### Técnicas de Otimização Avançadas

#### Subqueries Otimizadas

```php
// Subquery para contar relacionamentos
$users = User::addSelect([
    'posts_count' => Post::selectRaw('count(*)')
        ->whereColumn('author_id', 'users.id')
])->get();

// Subquery para última atividade
$users = User::addSelect([
    'last_post_date' => Post::select('created_at')
        ->whereColumn('author_id', 'users.id')
        ->latest()
        ->limit(1)
])->get();

// Subquery condicional
$posts = Post::addSelect([
    'is_liked' => Like::selectRaw('count(*) > 0')
        ->whereColumn('post_id', 'posts.id')
        ->where('user_id', auth()->id())
])->get();
```


#### Window Functions (PostgreSQL/MySQL 8.0+)

```php
// Ranking de posts por autor
$posts = Post::selectRaw('
    *,
    ROW_NUMBER() OVER (PARTITION BY author_id ORDER BY views DESC) as rank_by_author
')->get();

// Comparação com post anterior
$posts = Post::selectRaw('
    *,
    LAG(views) OVER (ORDER BY created_at) as previous_views
')->get();
```


## 13. Testes com Models

### Testes Unitários de Models

```php
<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_has_posts_relationship(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        
        $this->assertTrue($user->posts()->exists());
        $this->assertInstanceOf(Post::class, $user->posts->first());
        $this->assertEquals($post->id, $user->posts->first()->id);
    }
    
    public function test_user_full_name_accessor(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        
        $this->assertEquals('John Doe', $user->full_name);
    }
    
    public function test_user_password_is_hashed_when_set(): void
    {
        $user = new User();
        $user->password = 'plain-password';
        
        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(Hash::check('plain-password', $user->password));
    }
    
    public function test_user_scope_active(): void
    {
        $activeUser = User::factory()->create(['active' => true]);
        $inactiveUser = User::factory()->create(['active' => false]);
        
        $activeUsers = User::active()->get();
        
        $this->assertCount(1, $activeUsers);
        $this->assertTrue($activeUsers->contains($activeUser));
        $this->assertFalse($activeUsers->contains($inactiveUser));
    }
    
    public function test_user_soft_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        
        $user->delete();
        
        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNotNull($user->fresh()->deleted_at);
    }
}
```


### Uso de Factories em Testes

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();
        
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ];
        
        $response = $this->actingAs($user)
            ->post('/posts', $postData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'author_id' => $user->id,
        ]);
    }
    
    public function test_post_with_comments_can_be_retrieved(): void
    {
        $post = Post::factory()
            ->has(Comment::factory()->count(3))
            ->create();
        
        $response = $this->get("/posts/{$post->id}");
        
        $response->assertOk();
        $response->assertViewHas('post');
        
        $viewPost = $response->viewData('post');
        $this->assertCount(3, $viewPost->comments);
    }
    
    public function test_published_posts_are_visible_to_guests(): void
    {
        $publishedPosts = Post::factory()->count(3)->published()->create();
        $draftPosts = Post::factory()->count(2)->draft()->create();
        
        $response = $this->get('/posts');
        
        $response->assertOk();
        
        foreach ($publishedPosts as $post) {
            $response->assertSee($post->title);
        }
        
        foreach ($draftPosts as $post) {
            $response->assertDontSee($post->title);
        }
    }
}
```


### Mocking de Models

```php
<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserService;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function test_get_active_users_count(): void
    {
        // Mock do model User
        $userMock = Mockery::mock('alias:' . User::class);
        $userMock->shouldReceive('active')
                 ->once()
                 ->andReturnSelf();
        $userMock->shouldReceive('count')
                 ->once()
                 ->andReturn(5);
        
        $service = new UserService();
        $count = $service->getActiveUsersCount();
        
        $this->assertEquals(5, $count);
    }
    
    public function test_send_welcome_email_to_new_users(): void
    {
        // Mock parcial - mantém funcionalidade original mas mocka métodos específicos
        $user = User::factory()->make();
        $userMock = Mockery::mock($user)->makePartial();
        $userMock->shouldReceive('sendWelcomeEmail')
                 ->once()
                 ->andReturn(true);
        
        $service = new UserService();
        $result = $service->processNewUser($userMock);
        
        $this->assertTrue($result);
    }
}
```


### Assertions Específicas para Models

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseAssertionsTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_database_assertions(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        // Verificar se registro existe no banco
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        // Verificar modelo específico
        $this->assertModelExists($user);
        
        // Soft delete
        $user->delete();
        $this->assertSoftDeleted($user);
        
        // Verificar se não existe no banco
        $user->forceDelete();
        $this->assertModelMissing($user);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
    
    public function test_relationship_assertions(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        
        // Verificar relacionamentos
        $this->assertTrue($user->posts()->exists());
        $this->assertEquals(1, $user->posts()->count());
        $this->assertTrue($user->posts->contains($post));
        
        // Verificar relacionamento inverso
        $this->assertEquals($user->id, $post->author->id);
    }
    
    public function test_event_assertions(): void
    {
        Event::fake();
        
        $user = User::factory()->create();
        
        // Verificar se evento foi disparado
        Event::assertDispatched(UserCreated::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }
    
    public function test_queue_assertions(): void
    {
        Queue::fake();
        
        $user = User::factory()->create();
        $user->sendWelcomeEmail();
        
        // Verificar se job foi enfileirado
        Queue::assertPushed(SendWelcomeEmail::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }
}
```


### Testes de Performance

```php
<?php

namespace Tests\Performance;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryPerformanceTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_eager_loading_prevents_n_plus_one(): void
    {
        // Criar dados de teste
        $users = User::factory()->count(10)->create();
        $users->each(function ($user) {
            Post::factory()->count(5)->create(['author_id' => $user->id]);
        });
        
        // Contar queries sem eager loading
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        $posts = Post::all();
        $posts->each(function ($post) {
            $authorName = $post->author->name; // Causa N+1
        });
        
        $queriesWithoutEagerLoading = count(DB::getQueryLog());
        
        // Contar queries com eager loading
        DB::flushQueryLog();
        
        $posts = Post::with('author')->get();
        $posts->each(function ($post) {
            $authorName = $post->author->name;
        });
        
        $queriesWithEagerLoading = count(DB::getQueryLog());
        
        // Eager loading deve usar significativamente menos queries
        $this->assertLessThan($queriesWithoutEagerLoading / 2, $queriesWithEagerLoading);
        $this->assertEquals(2, $queriesWithEagerLoading); // 1 para posts, 1 para authors
    }
    
    public function test_chunking_handles_large_datasets(): void
    {
        // Criar dataset grande
        User::factory()->count(1000)->create();
        
        $processedCount = 0;
        $memoryBefore = memory_get_usage(true);
        
        User::chunk(100, function ($users) use (&$processedCount) {
            $processedCount += $users->count();
        });
        
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        $this->assertEquals(1000, $processedCount);
        // Verificar se o uso de memória está dentro de limites aceitáveis
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed); // Menos de 50MB
    }
}
```

Este documento abrangente cobre todos os aspectos avançados dos Models no Laravel, desde conceitos básicos até técnicas sofisticadas de otimização e testes. Cada seção inclui exemplos práticos e implementações reais que podem ser aplicadas em projetos Laravel de qualquer escala[^1][^2][^3][^4][^5][^12][^18][^13][^20][^10][^21][^22][^14][^23][^31][^32][^33][^34][^35].

<div style="text-align: center">⁂</div>

[^1]: https://laravel-docs-pt-br.readthedocs.io/en/latest/eloquent/

[^2]: https://www.dio.me/articles/desvendando-o-eloquent-orm-como-ele-simplifica-o-desenvolvimento-no-laravel

[^3]: https://www.dialhost.com.br/blog/eloquent-simplificando-models-laravel/

[^4]: https://laravel.com/docs/12.x/eloquent

[^5]: https://www.linkedin.com/pulse/understanding-laravels-fillable-vs-guarded-shashika-nuwan

[^6]: https://codexion.nl/articles/laravel-guarded-vs-fillable-in-models

[^7]: https://dev.to/kepsondiaz/fillable-guarded-in-laravel-whats-the-difference-589j

[^8]: https://caesardev.se/blogg/navigating-the-hazards-of-mass-assignment-in-laravel

[^9]: https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html

[^10]: https://laravel.com/docs/12.x/eloquent-serialization

[^11]: https://www.w3resource.com/laravel/eloquent-serialization.php

[^12]: https://www.honeybadger.io/blog/custom-laravel-casts/

[^13]: https://laravel.com/docs/12.x/eloquent-mutators

[^14]: https://fideloper.com/laravel-multiple-database-connections

[^15]: https://wpwebinfotech.com/blog/laravel-multiple-databases/

[^16]: https://laravel-docs.readthedocs.io/en/stable/eloquent-mutators/

[^17]: https://stackoverflow.com/questions/70480590/what-is-the-difference-between-accessor-mutators-and-casting-in-laravel

[^18]: https://laravel-news.com/query-scopes

[^19]: https://laravel-news.com/local-model-scopes-in-laravel-with-the-scope-attribute

[^20]: https://nabilhassen.com/3-simple-ways-to-use-eloquent-model-events-in-laravel

[^21]: https://laravel-news.com/working-with-laravel-model-events

[^22]: https://laravel-news.com/model-events

[^23]: https://benjamincrozat.com/laravel-soft-deletes

[^24]: https://dev.to/msnmongare/implementing-soft-deletes-in-laravel-safely-managing-deleted-data-22k

[^25]: https://beyondco.de/blog/a-guide-to-soft-delete-models-in-laravel

[^26]: https://laravel-news.com/package/thedoctor0-laravel-factory-generator

[^27]: https://laravel-news.com/automatically-create-model-factories

[^28]: https://www.inmotionhosting.com/support/edu/laravel/creating-laravel-database-model-factories/

[^29]: https://laravel.com/docs/7.x/eloquent-serialization

[^30]: https://spatie.be/docs/laravel-multitenancy/v4/installation/using-multiple-databases

[^31]: https://dev.to/devbabs/laravel-advanced-eloquent-orm-techniques-129e

[^32]: https://laravel.com/docs/8.x/database-testing

[^33]: https://dev.to/asekhamejoel/laravel-factories-seeders-automate-your-database-testing-like-a-pro-239k

[^34]: https://laravel-news.com/laravel-model-factories

[^35]: https://laravel.com/docs/12.x/database-testing

[^36]: https://imasters.com.br/php/3-tipos-de-relacionamento-entre-tabelas-no-laravel

[^37]: https://paulodev367.com.br/2024/01/11/explorando-a-elegancia-da-persistencia-de-dados-guia-completo-sobre-o-uso-do-eloquent-no-laravel/

[^38]: https://matheusteixeira.com.br/elaborando-relacionamentos-entre-modelos-no-laravel/

[^39]: https://andersonarruda.com.br/article/LaravelEntendendoModel/22

[^40]: https://kinsta.com/pt/blog/relacoes-eloquent-laravel/

[^41]: https://pt.stackoverflow.com/questions/329882/exemplo-prático-de-se-trabalhar-com-eloquent-no-laravel-5-6-relacionamentos

[^42]: https://laraveling.tech/novidades-laravel-8-models-e-factories/

[^43]: https://www.treinaweb.com.br/curso/laravel-eloquent-orm

[^44]: https://www.youtube.com/watch?v=uQRhHmeUzD8

[^45]: https://dev.to/marciopolicarpo/orm-eloquent-model-361g

[^46]: https://codebr.net/artigo/laravel-guia-completo-desenvolvimento-web-php

[^47]: https://www.mongodb.com/pt-br/docs/drivers/php/laravel-mongodb/v4.x/eloquent-models/relationships/

[^48]: https://awari.com.br/guia-completo-para-dar-os-primeiros-passos-no-laravel/

[^49]: https://codebr.net/artigo/entendendo-relacionamentos-models-laravel

[^50]: https://laravel.com

[^51]: https://www.devmedia.com.br/guia/laravel/38191

[^52]: https://blog.taller.net.br/laravel-filtrando-queries-utilizando-scopes/

[^53]: https://stackoverflow.com/questions/39616009/whats-the-difference-between-fillable-and-guard-in-laravel

[^54]: https://laravel.com/docs/7.x/eloquent-mutators

[^55]: https://stackoverflow.com/questions/21812826/default-scope-for-eloquent-models

[^56]: https://www.reddit.com/r/laravel/comments/ten0vn/help_me_understand_why_fillable_and_guarded_are/

[^57]: https://www.youtube.com/watch?v=S5Vc9Xflcx4

[^58]: https://www.youtube.com/watch?v=k2bS-RTU3Jg

[^59]: https://stackoverflow.com/questions/40567925/using-laravel-casts-and-mutators-at-the-same-time

[^60]: https://laracasts.com/discuss/channels/eloquent/global-query-scope-and-related-model-method

[^61]: https://laracasts.com/discuss/channels/eloquent/guarded-vs-fillable

[^62]: https://laracasts.com/discuss/channels/laravel/casts-and-mutators

[^63]: https://stackoverflow.com/questions/43633820/eloquent-model-observer-performance

[^64]: https://stackoverflow.com/questions/61086330/laravel-factory-using-custom-create-method

[^65]: https://github.com/laravel/framework/discussions/45570

[^66]: https://laravel.com/docs/12.x/eloquent-factories

[^67]: https://stackoverflow.com/questions/68575523/how-to-deserialize-a-laravel-eloquent-model-i-e-reverse-toarray-attributest

[^68]: https://laracasts.com/discuss/channels/eloquent/issue-registering-model-events-in-boot

[^69]: https://dev.to/rebelnii/how-to-build-a-custom-eloquent-builder-class-in-laravel-4bp8

[^70]: https://stackoverflow.com/questions/47182217/get-data-without-appends-and-relations-in-laravel-from-eloquent

[^71]: https://laravel.com/docs/12.x/events

[^72]: https://laracasts.com/discuss/channels/laravel/model-factory-in-custom-dir

[^73]: https://stackoverflow.com/questions/45588066/laravel-delete-affecting-other-timestamp-columns

[^74]: https://inspector.dev/create-and-use-custom-laravel-eloquent-collections/

[^75]: https://laravel.com/docs/5.0/eloquent?c=naoouvo

[^76]: https://www.yellowduck.be/posts/custom-collections-for-eloquent-models

[^77]: https://eoghanobrien.com/posts/define-a-custom-collection-for-your-eloquent-model

[^78]: https://martinjoo.dev/custom-eloquent-collections-in-laravel

[^79]: https://blog.devops.dev/laravel-multiple-database-setup-with-dynamic-database-switching-44cc2f86b4d9

[^80]: https://laravel.com/docs/12.x/eloquent-collections

[^81]: https://laravel.io/forum/multiple-database-connection-in-laravel

[^82]: https://neon.com/guides/laravel-soft-deletes

[^83]: https://laravel-docs.readthedocs.io/en/stable/eloquent-collections/

[^84]: https://www.youtube.com/watch?v=kj-SjBFcxl4

[^85]: https://www.honeybadger.io/blog/a-guide-to-soft-deletes-in-laravel/

[^86]: https://cubettech.com/resources/blog/scaling-your-laravel-app-proven-strategies-for-performance-optimization-and-reliability/

[^87]: https://mahekunnisa.hashnode.dev/advanced-laravel-eloquent-techniques-for-optimised-database-queries

[^88]: https://dev.to/vimuth7/understanding-mass-assignment-in-laravel-how-fillable-protects-your-models-2n4c

[^89]: https://deliciousbrains.com/optimizing-laravel-performance-basics/

[^90]: https://ashallendesign.co.uk/blog/mass-assignment-vulnerabilities-and-validation-in-laravel

[^91]: https://www.youtube.com/watch?v=GDNd3G8o1c4

[^92]: https://slashdev.io/-top-5-strategies-for-optimizing-laravel-performance-in-2024

[^93]: https://securinglaravel.com/in-depth-mass-assignment-vulnerabilities/

[^94]: https://stackoverflow.com/questions/49986380/laravel-factories-being-consumed-by-table-seeders-and-tests-best-practices-how

[^95]: https://wpwebinfotech.com/blog/advanced-laravel-eloquent-techniques/

[^96]: https://codecourse.com/articles/how-to-fix-add-column-to-fillable-property-to-allow-mass-assignment-on-model-in-laravel

[^97]: https://atyantik.com/laravel-performance-optimization-guide/

