# Controllers no Laravel: Guia Completo

Os **Controllers** no Laravel são uma das peças fundamentais da arquitetura MVC (Model-View-Controller) do framework, responsáveis por gerenciar a lógica de requisições e servir como intermediários entre as rotas e as views. Este guia abrangente explora desde conceitos básicos até aplicações avançadas de controllers.

## Introdução aos Controllers

**Controllers podem agrupar lógica de manipulação de requisições relacionadas em uma única classe**[^1]. **Em vez de definir toda a lógica de manipulação de requisições como closures em arquivos de rota, você pode organizar esse comportamento usando classes Controller**[^1].

**Por exemplo, uma classe `UserController` pode lidar com todas as requisições relacionadas a usuários, incluindo mostrar, criar, atualizar e deletar usuários**[^1]. **Por padrão, os controllers são armazenados no diretório `app/Http/Controllers`**[^1].

### Conceito Fundamental

**Um Controller é aquilo que controla o comportamento de uma requisição. Ele manipula as requisições vindas das rotas**[^2]. **Na arquitetura MVC, o 'C' representa 'Controller'**[^2]. **Os controllers desempenham um papel fundamental no desenvolvimento web com Laravel, sendo responsáveis por lidar com as solicitações**[^3].

## Criando Controllers

### Criando Controllers Básicos

**Para gerar rapidamente um novo controller, você pode executar o comando Artisan `make:controller`**[^4]:

```bash
php artisan make:controller UserController
```

**O comando acima criará um arquivo `UserController.php` dentro do diretório `/app/Http/Controllers`**[^5]. **Você pode especificar qualquer nome no lugar de 'User', mas de acordo com a convenção de nomenclatura do Laravel, deve especificar a palavra 'Controller' no final**[^2].

### Exemplo de Controller Básico

**Vamos ver um exemplo de um controller básico. Um controller pode ter qualquer número de métodos públicos que responderão a requisições HTTP**[^4]:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Mostrar o perfil de um usuário específico.
     */
    public function show(string $id): View
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }
}
```

**Uma vez que você tenha escrito uma classe e método de controller, pode definir uma rota para o método do controller**[^4]:

```php
use App\Http\Controllers\UserController;

Route::get('/user/{id}', [UserController::class, 'show']);
```

**Quando uma requisição corresponde à URI da rota especificada, o método `show` na classe `App\Http\Controllers\UserController` será invocado e os parâmetros da rota serão passados para o método**[^4].

### Controllers não são Obrigatórios para Estender uma Classe Base

**Controllers não são obrigatórios para estender uma classe base. No entanto, às vezes é conveniente estender uma classe controller base que contém métodos que devem ser compartilhados em todos os seus controllers**[^4]. **Contudo, você não terá acesso a recursos convenientes como os métodos `middleware` e `authorize`**[^1].

## Controllers de Ação Única

**Se uma ação do controller é particularmente complexa, você pode achar conveniente dedicar uma classe controller inteira para essa única ação**[^1]. **Para isso, você pode definir um único método `__invoke` dentro do controller**[^1]:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;

class ShowProfile extends Controller
{
    /**
     * Mostrar o perfil de um usuário específico.
     */
    public function __invoke($id)
    {
        return view('user.profile', [
            'user' => User::findOrFail($id)
        ]);
    }
}
```

**Ao registrar rotas para controllers de ação única, você não precisa especificar um método**[^6]:

```php
use App\Http\Controllers\ShowProfile;

Route::get('/user/{id}', ShowProfile::class);
```

**Você pode gerar um controller de ação única usando a opção `--invokable`**[^7]:

```bash
php artisan make:controller ShowProfile --invokable
```

**O `__invoke` é um método mágico do PHP que permite que o objeto seja chamado como uma função**[^7]. **Laravel usa isso nos bastidores durante a resolução de rotas para chamar apenas esse método específico dentro de uma classe controller**[^8].

## Resource Controllers

**Resource controllers fornecem uma maneira conveniente de criar controllers que lidam com operações CRUD (Create, Read, Update, Delete)**[^9]. **Usando resource controllers, você pode definir rapidamente todas as rotas necessárias para operações CRUD comuns**[^10].

### Criando Resource Controllers

```bash
php artisan make:controller PostController --resource
```

**Isso gerará automaticamente as seguintes rotas**[^10]:


| Método HTTP | URI | Ação | Nome da Rota |
| :-- | :-- | :-- | :-- |
| GET | /posts | index | posts.index |
| GET | /posts/create | create | posts.create |
| POST | /posts | store | posts.store |
| GET | /posts/{post} | show | posts.show |
| GET | /posts/{post}/edit | edit | posts.edit |
| PUT/PATCH | /posts/{post} | update | posts.update |
| DELETE | /posts/{post} | destroy | posts.destroy |

### Registrando Resource Routes

**Para registrar um resource controller, você usa o método `resource`**[^10]:

```php
Route::resource('posts', PostController::class);
```


### API Resource Controllers

**Para projetos de API, você pode usar `apiResource()` que cobre 5 métodos dos 7, excluindo os formulários visuais para create/edit**[^11]:

```php
Route::apiResource('posts', PostController::class);
```


### Customizando Resource Controllers

**Você pode especificar quais rotas gerar usando as opções `only` e `except`**[^9]:

```php
Route::resource('posts', PostController::class)->only(['index', 'show']);
Route::resource('posts', PostController::class)->except(['create', 'edit']);
```


## Middleware em Controllers

**Middleware no Laravel serve como um mecanismo para filtrar requisições HTTP**[^12]. **Você pode aplicar middleware a controllers específicos ou grupos para melhorar a segurança e gerenciar o controle de acesso**[^12].

### Aplicando Middleware no Constructor (Laravel 11+)

**Desde a versão 11 do Laravel, você precisa implementar a interface `HasMiddleware` para usar middleware em controllers**[^13]:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Middleware\IsAdmin;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class PostController extends Controller implements HasMiddleware
{
    /**
     * Obter o middleware que deve ser atribuído ao controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(IsAdmin::class, only: ['store', 'create', 'edit', 'update', 'destroy']),
        ];
    }
}
```


### Aplicando Middleware em Versões Anteriores

**Em versões anteriores do Laravel, você pode aplicar middleware diretamente no constructor**[^14]:

```php
class UserController extends Controller
{
    public function __construct()
    {
        // Aplicar a todos os métodos
        $this->middleware('auth');
        
        // Aplicar apenas a métodos específicos
        $this->middleware('auth')->only('create');
        
        // Aplicar exceto métodos específicos
        $this->middleware('auth')->except('index');
    }
}
```


## Dependency Injection em Controllers

**A injeção de dependência (DI) é um padrão de design que permite o desacoplamento de dependências hard-coded, tornando o código mais flexível, reutilizável e mais fácil de testar**[^15]. **O container de serviços do Laravel é uma ferramenta poderosa para gerenciar dependências de classe**[^15].

### Injeção no Constructor

**Você pode injetar dependências no constructor do controller**[^16]:

```php
<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
}
```


### Injeção em Métodos

**Você também pode injetar dependências diretamente em métodos do controller**[^16]:

```php
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $name = $request->input('name');
        // Lógica do controller
    }
}
```

**Para obter uma instância da requisição HTTP atual via injeção de dependência, você deve fazer type-hint da classe `Illuminate\Http\Request` no constructor ou método do controller**[^17].

## Validação em Controllers

**A validação é uma parte fundamental de qualquer aplicação. O Laravel fornece várias abordagens para validar dados de entrada**[^18].

### Validação Direta no Controller

**Você pode validar diretamente no controller usando o método `validate`**[^19]:

```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email',
        'age' => 'required|integer'
    ]);

    // Lógica após validação
}
```


### Form Request Validation

**Para validação mais complexa, você pode usar Form Request classes**[^20]:

```bash
php artisan make:request StoreUserRequest
```

```php
public function store(StoreUserRequest $request)
{
    // Os dados já foram validados
    $validated = $request->validated();
}
```


## Respostas de Controllers

**Controllers podem retornar diferentes tipos de respostas**[^21]:

### Retornando Strings e Arrays

```php
public function index()
{
    return 'Hello World';
}

public function data()
{
    return [1, 2, 3]; // Automaticamente convertido para JSON
}
```


### Retornando Views

```php
public function show($id)
{
    return view('users.profile', compact('user'));
}
```


### Retornando Responses Customizadas

```php
public function custom()
{
    return response('Hello World', 200)
        ->header('Content-Type', 'text/plain');
}
```


## Namespaces em Controllers

**Para manter controllers organizados, você pode usar namespaces customizados**[^22]:

### Criando Controllers em Namespaces

```bash
php artisan make:controller Admin/UserController --resource
```

**Isso criará o controller em `app/Http/Controllers/Admin/UserController.php`**[^22].

### Roteamento com Namespaces

```php
Route::namespace('Admin')->group(function() {
    Route::resource('users', UserController::class);
});
```


## Testes de Controllers

**Testar controllers é essencial para garantir que sua aplicação funciona corretamente**[^23]. **Testes de controller devem verificar respostas, garantir que os métodos corretos de acesso ao banco de dados são acionados e verificar que as variáveis de instância apropriadas são enviadas para a view**[^23].

### Exemplo de Teste

```php
/** @test */
public function it_shows_user_profile()
{
    $user = User::factory()->create();
    
    $response = $this->get("/users/{$user->id}");
    
    $response->assertOk();
    $response->assertViewIs('users.profile');
    $response->assertViewHas('user', $user);
}
```


### Testando Validação

```php
/** @test */
public function it_requires_name_field()
{
    $this->post('/users', [])
        ->assertSessionHasErrors('name');
}
```


## Executando Comandos Artisan em Controllers

**Se você realmente quiser chamar um comando Laravel de um controller, pode usar `Artisan::call()`**[^24]:

```php
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function runCommand()
    {
        $exitCode = Artisan::call('cache:clear');
        
        return 'Comando executado com sucesso!';
    }
}
```


## Organizando Controllers

### Controllers Genéricos

**Para aplicações com múltiplos recursos, você pode criar controllers genéricos que lidam com operações CRUD para qualquer modelo**[^25]:

```php
class GenericController extends Controller
{
    protected function getModel($modelName)
    {
        $modelClass = 'App\\Models\\' . Str::studly($modelName);
        
        if (!class_exists($modelClass)) {
            abort(404, "Model $modelName not found.");
        }
        
        return new $modelClass;
    }
    
    public function index($model)
    {
        $modelInstance = $this->getModel($model);
        return response()->json($modelInstance::all());
    }
}
```


### Refatoração de Controllers

**Para manter controllers limpos, você pode usar Services, Events, Jobs e Actions**[^26]:

```php
class UserController extends Controller
{
    public function store(StoreUserRequest $request, UserService $userService)
    {
        $user = $userService->createUser($request);
        
        return redirect()->route('users.index');
    }
}
```


## Comandos Artisan Relacionados

**Laravel fornece vários comandos artisan úteis para trabalhar com controllers**[^27]:

```bash
# Criar controller básico
php artisan make:controller UserController

# Criar resource controller
php artisan make:controller UserController --resource

# Criar controller com model
php artisan make:controller UserController --model=User

# Criar controller invokable
php artisan make:controller ShowProfile --invokable

# Listar todas as rotas
php artisan route:list
```


## Conclusão

Os Controllers no Laravel são elementos essenciais para organizar a lógica de aplicação de forma limpa e maintível. **Desde controllers básicos até funcionalidades avançadas como middleware, dependency injection, resource controllers e validação, o Laravel fornece todas as ferramentas necessárias para criar uma arquitetura de controllers robusta e flexível**[^1][^4].

**A compreensão profunda desses conceitos permite aos desenvolvedores criar aplicações mais organizadas, testáveis e escaláveis**, aproveitando ao máximo os recursos poderosos que o framework oferece para gerenciamento de controllers e lógica de negócio.

<div style="text-align: center">⁂</div>

[^1]: https://laravel.com/docs/8.x/controllers

[^2]: https://www.geeksforgeeks.org/php/laravel-controller-basics/

[^3]: https://community.revelo.com.br/controllers-no-laravel-simplificando-o-desenvolvimento-web/

[^4]: https://laravel.com/docs/12.x/controllers

[^5]: https://dev.to/codeanddeploy/create-controller-in-laravel-8-using-artisan-command-4bj2

[^6]: https://docs.w3cub.com/laravel~8/docs/8.x/controllers.html

[^7]: https://dev.to/lamhoanganh/laravel-cheat-sheet-controllers-a6a

[^8]: https://aschmelyun.com/blog/using-single-action-controllers-in-laravel/

[^9]: https://wpwebinfotech.com/blog/resource-controller-laravel/

[^10]: https://www.digitalocean.com/community/tutorials/simple-laravel-crud-with-resource-controllers

[^11]: https://lbodev.com.br/glossario/o-que-e-laravel-resource-controllers/

[^12]: https://laraveldaily.com/post/middleware-laravel-main-things-to-know

[^13]: https://gergotar.com/blog/posts/how-to-use-middleware-in-laravel-controllers

[^14]: https://laraveldaily.com/post/laravel-middleware-routes-controller

[^15]: https://www.sitepoint.com/dependency-injection-laravels-ioc/

[^16]: https://stackoverflow.com/questions/42817496/laravel-controller-dependency-injection

[^17]: https://readouble.com/laravel/5.1/en/requests.html

[^18]: https://laravel.com/docs/12.x/validation

[^19]: https://laravel-news.com/laravel-validation-101-controllers-form-requests-and-rules

[^20]: https://www.laravelactions.com/2.x/add-validation-to-controllers.html

[^21]: https://readouble.com/laravel/10.x/en/responses.html

[^22]: https://dev.to/bobbyiliev/custom-namespaces-to-organize-your-laravel-controllers-28oa

[^23]: https://code.tutsplus.com/testing-laravel-controllers--net-31456t

[^24]: https://stackoverflow.com/questions/37236206/run-artisan-command-in-laravel-5

[^25]: https://dev.to/abdullahqasim/how-to-build-a-generic-crud-controller-in-laravel-for-multiple-resources-1n9

[^26]: https://laravel-news.com/controller-refactor

[^27]: https://www.geeksforgeeks.org/php/laravel-artisan-commands-to-know-in-laravel/

[^28]: https://www.laravelactions.com/2.x/register-as-controller.html

[^29]: https://laravel.com/docs/7.x/controllers

[^30]: https://laravel-news.com/organize-laravel-applications-with-actions

[^31]: https://www.youtube.com/watch?v=KyzkOVnYSes

[^32]: https://www.laravelactions.com/1.x/actions-as-controllers.html

[^33]: https://laravel-docs-pt-br.readthedocs.io/en/latest/controllers/

[^34]: https://www.youtube.com/watch?v=HNTsM2ZmoFQ

[^35]: https://laravel.com/docs/6.x/controllers

[^36]: https://tutorials.ducatindia.com/laravel/laravel-controller

[^37]: https://stackoverflow.com/questions/39898893/deciding-which-controlleraction-to-be-called-based-on-a-given-parameters

[^38]: https://docs.golaravel.com/docs/7.x/controllers

[^39]: https://www.youtube.com/watch?v=bTHRxqiu3QM

[^40]: https://kinsta.com/blog/laravel-crud/

[^41]: https://www.youtube.com/watch?v=xOxTdXicWd0

[^42]: https://stackoverflow.com/questions/31301972/middleware-for-actions-into-controller-laravel-5-1

[^43]: https://dev.to/varzoeaa/laravel-middleware-magic-use-cases-you-didnt-know-about-593e

[^44]: https://www.interserver.net/tips/kb/mastering-laravels-service-container-for-dependency-injection/

[^45]: https://stackoverflow.com/questions/43804752/laravel-adding-middleware-inside-a-controller-function

[^46]: https://dev.to/aleson-franca/laravel-service-container-do-you-understand-whats-behind-app-and-dependency-injection--5hcl

[^47]: https://laravel.com/docs/12.x/container

[^48]: https://dev.to/olodocoder/laravel-api-series-controllers-crud-routing-and-search-functionality-4aho

[^49]: https://laravel.com/docs/12.x/middleware

[^50]: https://www.reddit.com/r/laravel/comments/ultj4k/how_to_return_a_response_and_view_in_the_same/

[^51]: https://laraveldaily.com/lesson/laravel-beginners/form-validation-controller-form-requests

[^52]: https://stackoverflow.com/questions/69750446/controller-and-request-in-laravel-in-my-case

[^53]: https://stackoverflow.com/questions/65921719/laravel-validate-inside-controller-without-make-customrequest

[^54]: https://stackoverflow.com/questions/55544331/how-to-show-a-view-in-controller-using-laravel

[^55]: https://laravel.com/docs/4.2/responses

[^56]: https://laravel-news.com/request-data-collection-handling

[^57]: https://www.youtube.com/watch?v=zDNF73Fdb5U

[^58]: https://lumen.laravel.com/docs/8.x/requests

[^59]: https://docs.w3cub.com/laravel~8/docs/8.x/validation.html

[^60]: https://stackoverflow.com/questions/66022737/can-laravel-controller-functions-return-multiple-views-joined-together

[^61]: https://readouble.com/laravel/5.2/en/requests.html

[^62]: https://dev.to/mattdaneshvar/testing-validation-in-laravel-5fdj

[^63]: https://stackoverflow.com/questions/22871503/how-to-do-simple-testing-of-a-laravel-controller

[^64]: https://laravel.com/docs/12.x/artisan

[^65]: https://laravel.io/forum/04-03-2014-controllers-namespaces

[^66]: https://stackoverflow.com/questions/23959302/whats-the-proper-approach-to-testing-controllers-in-laravel

[^67]: https://stackoverflow.com/questions/72725871/using-controllerclass-in-routeresource-is-causing-namespace-to-be-included-t

[^68]: https://stackoverflow.com/questions/23512489/how-to-use-different-namespaces-in-a-controller-in-laravel-4-1

[^69]: https://christoph-rumpel.com/2023/3/everything-you-can-test-in-your-laravel-application

[^70]: https://laravel.com/docs/5.1/controllers

[^71]: https://stackoverflow.com/questions/24887777/laravel-unit-testing-of-controllers

[^72]: https://laravel.com/docs/5.2/artisan

[^73]: https://stackoverflow.com/questions/29781103/how-to-test-laravel-5-controllers-methods/29781882

[^74]: https://www.honeybadger.io/blog/laravel-artisan-processes/

