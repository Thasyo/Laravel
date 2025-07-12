# Routing no Laravel: Guia Completo

O sistema de **routing** (roteamento) é uma das funcionalidades mais fundamentais do Laravel, responsável por mapear requisições HTTP para controladores ou ações específicas. Este guia abrangente explora desde conceitos básicos até aplicações avançadas de rotas.

## Introdução ao Routing

O routing no Laravel permite **rotear todas as requisições da aplicação para seus controladores apropriados**[^1]. As rotas principais no Laravel reconhecem e aceitam uma URI (Uniform Resource Identifier) junto com uma closure, proporcionando uma maneira simples e expressiva de definir rotas[^1].

### Arquivos de Rotas Padrão

**Todas as rotas do Laravel são definidas nos arquivos de rota localizados no diretório `routes`**[^2][^3]. Esses arquivos são carregados automaticamente pelo framework através da configuração especificada no arquivo `bootstrap/app.php` da aplicação[^3][^4].

Os principais arquivos de rota são:

- **`routes/web.php`**: Define rotas para a interface web, atribuídas ao grupo de middleware `web`, que fornece recursos como estado de sessão e proteção CSRF[^2][^3]
- **`routes/api.php`**: Define rotas para APIs, que são stateless e atribuídas ao grupo de middleware `api`[^2][^5]


## Routing Básico

### Definindo Rotas Simples

A forma mais básica de definir uma rota no Laravel aceita uma URI e uma closure (função callback):

```php
use Illuminate\Support\Facades\Route;

Route::get('/greeting', function () {
    return 'Hello World';
});
```

**Esta é a maneira mais simples e expressiva de definir rotas e comportamentos sem arquivos de configuração complicados**[^3][^4].

### Métodos HTTP Disponíveis

O roteador permite registrar rotas que respondem a qualquer verbo HTTP[^2][^3]:

```php
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
```


#### Rotas para Múltiplos Verbos

**Às vezes você pode precisar registrar uma rota que responde a múltiplos verbos HTTP**[^2][^3]. Você pode fazer isso usando o método `match`:

```php
Route::match(['get', 'post'], '/', function () {
    // ...
});
```

Ou registrar uma rota que responde a todos os verbos HTTP usando o método `any`:

```php
Route::any('/', function () {
    // ...
});
```


### Rotas de Redirecionamento e View

**Se você está definindo uma rota que redireciona para outra URI, pode usar o método `Route::redirect`**[^3]:

```php
Route::redirect('/here', '/there');

// Com código de status customizado
Route::redirect('/here', '/there', 301);

// Redirecionamento permanente
Route::permanentRedirect('/here', '/there');
```

**Para rotas que apenas retornam uma view, você pode usar o método `Route::view`**[^3]:

```php
Route::view('/welcome', 'welcome');

// Com dados passados para a view
Route::view('/welcome', 'welcome', ['name' => 'Taylor']);
```


### Proteção CSRF

**Qualquer formulário HTML apontando para rotas `POST`, `PUT`, `PATCH` ou `DELETE` definidas no arquivo de rotas web deve incluir um campo de token CSRF**[^2][^3]. Caso contrário, a requisição será rejeitada:

```php
<form method="POST" action="/profile">
    @csrf
    ...
</form>
```


## Parâmetros de Rota

### Parâmetros Obrigatórios

**Às vezes você precisará capturar segmentos da URI dentro de sua rota**[^3]. Por exemplo, capturar o ID de um usuário da URL:

```php
Route::get('/user/{id}', function (string $id) {
    return 'User '.$id;
});
```

**Você pode definir quantos parâmetros de rota forem necessários**[^3]:

```php
Route::get('/posts/{post}/comments/{comment}', function (string $postId, string $commentId) {
    // ...
});
```

**Os parâmetros de rota são sempre envolvidos em chaves `{}` e devem consistir em caracteres alfabéticos**[^3]. Underscores (`_`) também são aceitáveis nos nomes dos parâmetros.

### Parâmetros Opcionais

**Ocasionalmente você pode precisar especificar um parâmetro de rota que pode nem sempre estar presente na URI**[^3]. Você pode fazer isso colocando uma marca `?` após o nome do parâmetro:

```php
Route::get('/user/{name?}', function (?string $name = null) {
    return $name;
});

Route::get('/user/{name?}', function (?string $name = 'John') {
    return $name;
});
```


### Restrições com Expressões Regulares

**Você pode restringir o formato dos parâmetros de rota usando o método `where` em uma instância de rota**[^3][^6]:

```php
Route::get('/user/{name}', function (string $name) {
    // ...
})->where('name', '[A-Za-z]+');

Route::get('/user/{id}', function (string $id) {
    // ...
})->where('id', '[0-9]+');
```

Para conveniência, alguns padrões de expressão regular comumente usados têm métodos auxiliares[^3][^7]:

```php
Route::get('/user/{id}/{name}', function (string $id, string $name) {
    // ...
})->whereNumber('id')->whereAlpha('name');

Route::get('/user/{name}', function (string $name) {
    // ...
})->whereAlphaNumeric('name');

Route::get('/user/{id}', function (string $id) {
    // ...
})->whereUuid('id');
```


### Restrições Globais

**Se você gostaria que um parâmetro de rota fosse sempre restrito por uma determinada expressão regular, pode usar o método `pattern`**[^3]. Você deve definir esses padrões no método `boot` da classe `App\Providers\AppServiceProvider`:

```php
use Illuminate\Support\Facades\Route;

public function boot(): void
{
    Route::pattern('id', '[0-9]+');
}
```


## Rotas Nomeadas

**Rotas nomeadas permitem a geração conveniente de URLs ou redirecionamentos para rotas específicas**[^3][^8]. **Você pode especificar um nome para uma rota encadeando o método `name` na definição da rota**[^3]:

```php
Route::get('/user/profile', function () {
    // ...
})->name('profile');
```

**Você também pode especificar nomes de rota para ações de controlador**[^3]:

```php
Route::get(
    '/user/profile',
    [UserProfileController::class, 'show']
)->name('profile');
```


### Gerando URLs para Rotas Nomeadas

**Uma vez que você tenha atribuído um nome a uma rota, pode usar o nome da rota ao gerar URLs ou redirecionamentos através das funções auxiliares `route` e `redirect` do Laravel**[^3][^8]:

```php
// Gerando URLs...
$url = route('profile');

// Gerando Redirecionamentos...
return redirect()->route('profile');
return to_route('profile');
```

**Se a rota nomeada define parâmetros, você pode passar os parâmetros como segundo argumento para a função `route`**[^3]:

```php
Route::get('/user/{id}/profile', function (string $id) {
    // ...
})->name('profile');

$url = route('profile', ['id' => 1]);
```


### Vantagens das Rotas Nomeadas

**O principal benefício das rotas nomeadas é a facilidade de manutenção**[^8]. **Em vez de codificar URLs diretamente em sua aplicação, você pode referenciar apenas rotas nomeadas e nunca as URLs reais**[^8]. Se você precisar alterar a URL de alguma requisição, ela será automaticamente resolvida para a URL correta em todos os lugares.

## Grupos de Rotas

**Grupos de rotas permitem compartilhar atributos de rota, como middleware, através de um grande número de rotas sem precisar definir esses atributos em cada rota individual**[^3][^9].

### Middleware

**Para atribuir middleware a todas as rotas dentro de um grupo, você pode usar o método `middleware` antes de definir o grupo**[^3]:

```php
Route::middleware(['first', 'second'])->group(function () {
    Route::get('/', function () {
        // Usa first & second middleware...
    });

    Route::get('/user/profile', function () {
        // Usa first & second middleware...
    });
});
```


### Controladores

**Se um grupo de rotas utiliza o mesmo controlador, você pode usar o método `controller` para definir o controlador comum**[^3]:

```php
use App\Http\Controllers\OrderController;

Route::controller(OrderController::class)->group(function () {
    Route::get('/orders/{id}', 'show');
    Route::post('/orders', 'store');
});
```


### Prefixos de Rota

**O método `prefix` pode ser usado para prefixar cada rota no grupo com uma determinada URI**[^3][^9]:

```php
Route::prefix('admin')->group(function () {
    Route::get('/users', function () {
        // Corresponde à URL "/admin/users"
    });
});
```


### Prefixos de Nome de Rota

**O método `name` pode ser usado para prefixar cada nome de rota no grupo com uma determinada string**[^3][^9]:

```php
Route::name('admin.')->group(function () {
    Route::get('/users', function () {
        // Rota atribuída ao nome "admin.users"...
    })->name('users');
});
```


### Roteamento de Subdomínio

**Grupos de rotas também podem ser usados para lidar com roteamento de subdomínio**[^3]. **Subdomínios podem receber parâmetros de rota assim como URIs de rota**:

```php
Route::domain('{account}.example.com')->group(function () {
    Route::get('/user/{id}', function (string $account, string $id) {
        // ...
    });
});
```


## Model Binding de Rotas

**Ao injetar um ID de modelo em uma rota ou ação de controlador, você frequentemente consultará o banco de dados para recuperar o modelo que corresponde a esse ID**[^3][^10]. **O route model binding do Laravel fornece uma maneira conveniente de injetar automaticamente as instâncias do modelo diretamente em suas rotas**[^3][^10].

### Binding Implícito

**O Laravel resolve automaticamente modelos Eloquent definidos em rotas ou ações de controlador cujos nomes de variáveis type-hinted correspondem a um nome de segmento de rota**[^3][^11]:

```php
use App\Models\User;

Route::get('/users/{user}', function (User $user) {
    return $user->email;
});
```

**Como a variável `$user` é type-hinted como modelo Eloquent `App\Models\User` e o nome da variável corresponde ao segmento URI `{user}`, o Laravel injetará automaticamente a instância do modelo que tem um ID correspondente ao valor da URI da requisição**[^3][^11].

### Personalizando a Chave

**Às vezes você pode desejar resolver modelos Eloquent usando uma coluna diferente de `id`**[^3]. **Para fazer isso, você pode especificar a coluna na definição do parâmetro de rota**:

```php
use App\Models\Post;

Route::get('/posts/{post:slug}', function (Post $post) {
    return $post;
});
```

**Se você gostaria que o model binding sempre use uma coluna de banco de dados diferente de `id` ao recuperar uma determinada classe de modelo, pode sobrescrever o método `getRouteKeyName` no modelo Eloquent**[^3]:

```php
/**
 * Get the route key for the model.
 */
public function getRouteKeyName(): string
{
    return 'slug';
}
```


### Binding Explícito

**Você não é obrigado a usar a resolução implícita baseada em convenção do Laravel para usar model binding**[^3]. **Você também pode definir explicitamente como os parâmetros de rota correspondem aos modelos**:

```php
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Route::model('user', User::class);
}
```


## Rotas Resource

**Rotas de resource fornecem uma maneira de definir um conjunto de rotas que mapeiam para várias operações CRUD (Create, Read, Update, Delete) para um controlador resourceful**[^12][^13]. **Usando rotas de resource, você pode definir rapidamente todas as rotas necessárias para sua aplicação em uma única linha de código**[^12]:

```php
Route::resource('posts', PostController::class);
```

Isso gera automaticamente as seguintes rotas[^12][^13]:


| Método | URI | Ação | Nome da Rota |
| :-- | :-- | :-- | :-- |
| GET | /posts | index | posts.index |
| GET | /posts/create | create | posts.create |
| POST | /posts | store | posts.store |
| GET | /posts/{post} | show | posts.show |
| GET | /posts/{post}/edit | edit | posts.edit |
| PUT/PATCH | /posts/{post} | update | posts.update |
| DELETE | /posts/{post} | destroy | posts.destroy |

### API Resource Routes

**Para projetos de API, você pode usar `apiResource()` que cobre 5 métodos dos 7, excluindo os formulários visuais para create/edit**[^14][^15]:

```php
Route::apiResource('posts', PostController::class);
```


### Customizando Resource Routes

**Você pode especificar quais rotas gerar usando as opções `only` e `except`**[^12]:

```php
Route::resource('posts', PostController::class)->only(['index', 'show', 'destroy']);

Route::resource('posts', PostController::class)->except(['create', 'edit']);
```


## Rotas Fallback

**Usando o método `Route::fallback`, você pode definir uma rota que será executada quando nenhuma outra rota corresponder à requisição de entrada**[^3][^16]. **Tipicamente, requisições não tratadas renderizarão automaticamente uma página "404" através do manipulador de exceções da aplicação**[^3]:

```php
Route::fallback(function () {
    return view('errors.404');
});
```

**As rotas fallback são úteis para criar experiências significativas para usuários que encontram páginas ausentes, em vez de mostrar uma página 404 genérica**[^17]. **Você pode usar isso para coletar dados sobre páginas ausentes, fazer redirecionamentos inteligentes ou fornecer sugestões alternativas**[^17].

## Rate Limiting

**O Laravel inclui serviços de limitação de taxa poderosos e customizáveis que você pode utilizar para restringir a quantidade de tráfego para uma determinada rota ou grupo de rotas**[^3].

### Definindo Rate Limiters

**Os limitadores de taxa podem ser definidos dentro do método `boot` da classe `App\Providers\AppServiceProvider`**[^3]:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

protected function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
}
```


### Anexando Rate Limiters às Rotas

**Os limitadores de taxa podem ser anexados às rotas ou grupos de rotas usando o middleware `throttle`**[^3]:

```php
Route::middleware(['throttle:uploads'])->group(function () {
    Route::post('/audio', function () {
        // ...
    });

    Route::post('/video', function () {
        // ...
    });
});
```


## Cache de Rotas

**Ao implantar sua aplicação em produção, você deve aproveitar o cache de rotas do Laravel**[^3][^18]. **Usar o cache de rotas diminuirá drasticamente a quantidade de tempo necessária para registrar todas as rotas da aplicação**[^3]:

```php
php artisan route:cache
```

**Após executar este comando, seu arquivo de rotas em cache será carregado em cada requisição**[^3]. **Lembre-se de que se você adicionar novas rotas, precisará gerar um cache de rotas novo**[^3]:

```php
php artisan route:clear
```

**O cache de rotas pode resultar em melhorias de performance de até 100x em alguns casos, especialmente em aplicações com muitas rotas**[^18].

## Middleware em Rotas

**Middleware no Laravel serve como um mecanismo para filtrar requisições HTTP que entram em sua aplicação**[^19][^20]. **Você pode aplicar middleware a rotas específicas ou grupos para melhorar a segurança e gerenciar o controle de acesso efetivamente**[^19]:

```php
// Middleware em rota única
Route::get('/profile', [UserController::class, 'profile'])->middleware('auth');

// Middleware em resource
Route::resource('users', UserController::class)->middleware('auth');

// Middleware em grupo
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile', [ProfileController::class, 'update']);
});
```


## Listando Rotas

**O comando Artisan `route:list` pode facilmente fornecer uma visão geral de todas as rotas definidas pela aplicação**[^3]:

```php
php artisan route:list

// Com detalhes de middleware
php artisan route:list -v

// Filtrar por caminho
php artisan route:list --path=api

// Excluir rotas de pacotes
php artisan route:list --except-vendor
```


## Conclusão

O sistema de routing do Laravel oferece uma base sólida e flexível para construir aplicações web robustas. **Desde rotas básicas até funcionalidades avançadas como model binding, grupos de rotas e rate limiting, o Laravel fornece todas as ferramentas necessárias para criar uma arquitetura de routing limpa e maintível**[^3][^19].

**A compreensão profunda desses conceitos permite aos desenvolvedores criar aplicações mais organizadas, seguras e performáticas**[^19], aproveitando ao máximo os recursos poderosos que o framework oferece para gerenciamento de rotas.

[^1]: https://dev.to/codedthemes/explain-the-concept-of-laravel-routing-2kb2

[^2]: https://laravel-docs.readthedocs.io/en/stable/routing/

[^3]: https://laravel.com/docs/12.x/routing

[^4]: https://laravel.com/docs/11.x/routing

[^5]: https://docs.golaravel.com/docs/8.x/routing

[^6]: https://devinthewild.com/article/guide-to-using-where-constraints-laravel-routes

[^7]: https://dev.to/gregorip02/define-restrictions-to-your-laravel-routes-easy-24do

[^8]: https://stackoverflow.com/questions/56670372/what-are-some-use-cases-of-named-routes-in-laravel

[^9]: https://laraveldaily.com/post/how-to-structure-routes-in-large-laravel-projects

[^10]: https://dev.to/remonhasan/laravel-route-model-binding-simplifying-controller-logic-b1j

[^11]: https://dev.to/bijaykumarpun/route-model-binding-in-laravel-34ja

[^12]: https://dev.to/gurpreetkait/a-beginners-guide-to-resource-routes-in-laravel-4d2j

[^13]: https://larachamp.com/a-beginners-guide-to-resource-routes-in-laravel

[^14]: https://laravel-news.com/laravel-route-organization-tips

[^15]: https://dev.to/johndivam/laravel-routes-apiresource-vs-resource-ij5

[^16]: https://laraveldaily.com/post/route-fallback-if-no-other-route-is-matched

[^17]: https://laravel-news.com/route-fallback

[^18]: https://voltagead.com/laravel-route-caching-for-improved-performance/

[^19]: https://www.interserver.net/tips/kb/advanced-route-management-in-laravel-mastering-url-handling-and-middleware/

[^20]: https://laraveldaily.com/lesson/laravel-beginners/middleware-route-groups-auth

[^21]: https://www.w3schools.in/laravel/routing

[^22]: https://laravel.com/docs/4.2/routing

[^23]: https://www.youtube.com/watch?v=0KrDpSYDzE4

[^24]: https://www.geeksforgeeks.org/php/laravel-routing-basics/

[^25]: https://kinsta.com/blog/laravel-routes/

[^26]: https://www.youtube.com/watch?v=9kL1RdMywGo

[^27]: https://laravel-docs-pt-br.readthedocs.io/en/latest/routing/

[^28]: https://laraveldaily.com/lesson/laravel-beginners/basic-routing-urls-default-homepage

[^29]: https://www.youtube.com/watch?v=VHxobmTBiIU

[^30]: https://www.w3resource.com/laravel/laravel-routing.php

[^31]: https://www.youtube.com/watch?v=pSYu_XNkJ98

[^32]: https://laravel.com/docs/5.2/routing

[^33]: https://docs.w3cub.com/laravel~8/docs/8.x/routing.html

[^34]: https://laravel.com/docs/5.1/routing

[^35]: https://laravel.com/docs/7.x/routing

[^36]: https://dev.to/bhaidar/laravel-route-parameters-how-to-retrieve-and-use-them-m9c

[^37]: https://stackoverflow.com/questions/36838177/how-to-define-route-group-name-in-laravel

[^38]: https://stackoverflow.com/questions/42359582/laravel-route-with-parameters

[^39]: https://www.youtube.com/watch?v=awStsyqYcbc

[^40]: https://laraveldaily.com/lesson/laravel-beginners/route-parameters-route-model-binding

[^41]: https://www.tutorialspoint.com/what-are-named-routes-in-laravel

[^42]: https://stackoverflow.com/questions/56670372/what-are-some-use-cases-of-named-routes-in-laravel/56670398

[^43]: https://www.youtube.com/watch?v=ygsYjNMcd0U

[^44]: https://laracasts.com/discuss/channels/laravel/laravel-routes-best-practices

[^45]: https://www.youtube.com/watch?v=wnTp-1kI_2w

[^46]: https://laravel.com/docs/5.4/routing

[^47]: https://alemsbaja.hashnode.dev/how-to-use-route-groups-and-prefixes-in-laravel-11

[^48]: https://laravel.com/docs/5.3/routing

[^49]: https://laraveldaily.com/tip/custom-resource-route-names

[^50]: https://www.youtube.com/watch?v=ZX_bkEHJECA

[^51]: https://www.digitalocean.com/community/tutorials/get-laravel-route-parameters-in-middleware

[^52]: https://www.treinaweb.com.br/blog/como-se-usa-e-o-que-resolve-o-recurso-de-route-model-binding-do-laravel

[^53]: https://laraveldaily.com/post/laravel-middleware-routes-controller

[^54]: https://stackoverflow.com/questions/38745291/laravel-middleware-route-groups

[^55]: https://laravel.com/docs/12.x/controllers

[^56]: https://dev.to/arifiqbal/route-model-binding-in-laravel-4amk

[^57]: https://laravel.com/docs/12.x/middleware

[^58]: https://stackoverflow.com/questions/23505875/laravel-routeresource-vs-routecontroller

[^59]: https://www.educative.io/answers/what-is-route-model-binding-in-laravel

[^60]: https://laravel.com/docs/9.x/middleware

[^61]: https://spatie.be/docs/laravel-multitenancy/v4/using-tasks-to-prepare-the-environment/switching-route-cache-paths

[^62]: https://stackoverflow.com/questions/37878951/how-to-clear-laravel-route-caching-on-server

[^63]: https://alemsbaja.hashnode.dev/how-to-handle-access-to-undefined-routes-in-laravel-11-using-fallback-method

[^64]: https://stackoverflow.com/questions/22622629/regular-expression-route-constraint-for-a-resource-route

[^65]: https://masteringlaravel.io/daily/2024-12-18-how-do-fallback-routes-work

[^66]: https://lbodev.com.br/glossario/o-que-e-laravel-route-caching/

[^67]: https://www.youtube.com/watch?v=QauhB3-m8zM

[^68]: https://github.com/MaartenStaa/laravel-41-route-caching

[^69]: https://dev.to/buddhieash/laravel-wildcard-routes-fallback-routes-2k3a

[^70]: https://github.com/mnshankar/laravel-cache-route

