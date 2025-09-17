# Estudo Avançado sobre Middlewares

**Principais mensagens**
Middleware é a “cola” de software que conecta aplicações, dados e usuários, oferecendo serviços transversais como autenticação, integração e controle de tráfego. Dominar seus padrões arquiteturais e saber implementá-los em frameworks como Laravel é crucial para construir sistemas escaláveis, seguros e performáticos.

***

## 1. Conceitos Fundamentais

Middleware é o **software intermediário** que opera entre o sistema operativo e as aplicações, fornecendo funcionalidades além daquelas oferecidas pelo SO. Na pilha de rede, situa-se **acima do transporte (TCP/IP) e abaixo da lógica de negócio**, sendo “o hífen em client-server”.[^1][^2]

### 1.1 Evolução

- Termo usado desde 1968; popularizou-se nos anos 1980 para ligar aplicações novas a legados.[^3][^1]
- Hoje é base de arquiteturas cloud-native, microservices e edge computing.[^2][^4]

***

## 2. Principais Categorias e Tipos

| Categoria | Função-chave | Exemplos / Tecnologias |
| :-- | :-- | :-- |
| **Message-Oriented Middleware (MOM)** | Troca assíncrona de mensagens | Kafka, RabbitMQ[^5][^3] |
| **Remote Procedure Call (RPC)** | Invocar métodos remotos como locais | gRPC, JSON-RPC[^5][^3] |
| **Object Request Broker (ORB)** | Comunicação entre objetos distribuídos | CORBA[^5] |
| **Database Middleware** | Abstrair acesso a múltiplos BD | ODBC, JDBC[^5][^3] |
| **Transaction Processing** | Garantir ACID em ambientes distribuídos | CICS, Tuxedo[^5][^6] |
| **API Gateway / API Middleware** | Gerir, proteger e monitorar APIs | Kong, Apigee[^5][^3] |
| **Enterprise Service Bus (ESB)** | Roteamento e transformação | Mule, WSO2 ESB[^5][^7] |
| **Portais / CMS** | Agregar aplicações sob única interface | Liferay[^5] |

### 2.1 Benefícios

- **Desacoplamento** entre produtores e consumidores de dados.[^6][^2]
- **Escalabilidade** horizontal independente de cada serviço.[^8]
- **Segurança centralizada** (autenticação, criptografia, auditoria).[^9][^2]
- **Integração heterogênea** (diferentes linguagens e protocolos).[^10][^11]


### 2.2 Desafios

- **Latência e overhead** extra no caminho de requisições.[^12][^8]
- **Complexidade de configuração** e governança em grandes malhas.[^13][^9]
- **Implementações divergentes** podem gerar inconsistência de políticas.[^9][^13]

***

## 3. Padrões Arquiteturais

* **Chain of Responsibility** – requisição percorre uma cadeia de handlers.[^14]
* **Proxy** – intercepta chamadas para adicionar comportamento extra.[^14]
* **Microkernel / Mesh / Broker** – organização interna de um barramento.[^15]
* **Smart Endpoints, Dumb Pipes** – lógica nos serviços, middleware simples.[^16]
* **Middleware Pattern** vs **API Gateway Pattern** em AuthN/AuthZ.[^13][^9]

Em microservices, middleware garante **comunicação resiliente, observabilidade e circuit-breaker**.[^4][^8]

***

## 4. Melhores Práticas de Desempenho e Segurança

1. **Posicionar handlers leves primeiro** (arquivos estáticos, cache).[^17][^12]
2. **Minimizar camadas** e usar *conditional middleware* por rota.[^18]
3. **Operações assíncronas** no middleware (`async/await`) para evitar bloqueios.[^19][^12]
4. **Rate limiting** e **Zero Trust** no perímetro (throttle, verificação de token).[^20][^21]
5. **Monitorar e testar** continuamente throughput, latência e erros.[^22][^18]

***

## 5. Middleware no Laravel

### 5.1 Conceito

No Laravel, middleware é um **filtro HTTP** que inspeciona a requisição antes ou depois da aplicação, localizado em `app/Http/Middleware`. O método padrão é:[^23][^24]

```php
public function handle(Request $request, Closure $next)
{
    // lógica...
    return $next($request);   // passa adiante
}
```

Para tarefas após o envio da resposta, usa-se **Terminable Middleware** com o método `terminate()`.[^25][^26]

### 5.2 Tipos em Laravel

- **Global** – executa em todas as requisições, registrado em `bootstrap/app.php` ou `$middleware`.[^24]
- **Rota / Controlador** – aplicado via `->middleware()`.[^25][^24]
- **Grupos** – `web`, `api` ou customizados.[^27][^24]
- **Aliases** – atalhos como `auth`, `throttle`.[^24]

***

## 6. Exemplos Práticos em Laravel

### 6.1 Middleware de Autorização “admin”

```bash
php artisan make:middleware CheckAdmin
```

```php
// app/Http/Middleware/CheckAdmin.php
public function handle(Request $request, Closure $next)
{
    if (!$request->user() || !$request->user()->isAdmin()) {
        abort(403,'Admin access required');
    }
    return $next($request);
}
```

Usado na rota:

```php
Route::get('/admin/users', ...)->middleware('admin');
```


### 6.2 Limite de Tentativas de Login

```php
Route::post('/login', [LoginController::class,'authenticate'])
      ->middleware('throttle:5,1');   // 5 tentativas por minuto
```

Protege contra brute-force.[^28][^29]

### 6.3 Middleware com Parâmetros

```bash
php artisan make:middleware EnsureUserHasRole
```

```php
public function handle($request, Closure $next, string $role)
{
    if (!$request->user()->hasRole($role)) {
        return redirect('/');
    }
    return $next($request);
}
```

Uso:

```php
Route::put('/post/{id}', ...)->middleware('role:editor');
```


### 6.4 Middleware Terminável para Log Assíncrono

```bash
php artisan make:middleware PostResponseLogger
```

```php
class PostResponseLogger
{
    public function handle($req, Closure $next)
    {
        return $next($req);
    }

    public function terminate($req, $res): void
    {
        \Log::info('URL '.$req->fullUrl().' Status '.$res->status());
    }
}
```

Ideal para tarefas que não devem aumentar a latência da resposta.[^26][^30][^31]

### 6.5 Grupo Personalizado

```php
app()->middleware()->group('premium', [
    CheckSubscription::class,
    EnsureVerifiedEmail::class,
]);
```

```php
Route::middleware('premium')->group(function () {
    // rotas exclusivas
});
```

Facilita reuso e clareza.[^32][^27]

### 6.6 Rate Limiting Dinâmico por Papel

```php
RateLimiter::for('api', function (Request $request) {
    return $request->user()?->isAdmin()
        ? Limit::perMinute(100)
        : Limit::perMinute(20);
});
```


***

## 7. Injeção de Dependências em Middleware

Dependências devem ser **injetadas no construtor**; o método `handle` não é resolvido pelo contêiner. Ex.:[^33]

```php
class LogUA
{
    public function __construct(DeviceDetector $dd)
    {
        $this->dd = $dd;
    }
    ...
}
```

O Service Container do Laravel resolve automaticamente.[^34][^35]

***

## 8. Testes de Middleware

1. **Unitário** – instanciar `Request`, chamar `handle`, assert sobre resposta.[^36][^37]
2. **Feature** – criar rota fake que usa o middleware e fazer requisição via `get()`.[^38]
3. Ferramentas: PHPUnit ou Pest para sintaxe fluida de expectations.[^39][^40]

***

## 9. Conclusão

Middleware é peça central em integrações modernas, garantindo **segurança, observabilidade e desacoplamento**. Ao empregar boas práticas de ordenação, execução assíncrona e testes, minimiza-se o impacto de latência e maximizam-se desempenho e governança. No Laravel, a API de middleware fornece um pipeline elegante para **implementar políticas de autenticação, limitação de tráfego, monitoramento e pós-processamento** com poucas linhas de código, mantendo a aplicação limpa e modular.
<span style="display:none">[^41][^42][^43][^44][^45][^46][^47][^48][^49][^50][^51][^52][^53][^54][^55][^56][^57][^58][^59][^60][^61][^62][^63][^64][^65][^66][^67][^68][^69][^70][^71][^72][^73][^74][^75][^76][^77][^78][^79][^80][^81][^82][^83][^84][^85][^86][^87][^88][^89]</span>

<div style="text-align: center">⁂</div>

[^1]: https://en.wikipedia.org/wiki/Middleware

[^2]: https://www.redhat.com/en/topics/middleware/what-is-middleware

[^3]: https://www.ibm.com/think/topics/middleware

[^4]: https://wso2.com/blogs/thesource/2016/05/enabling-microservice-architecture-with-middleware/

[^5]: https://www.softcomputer.com/2024/03/02/what-are-the-6-types-of-middleware/

[^6]: https://www.geeksforgeeks.org/operating-systems/what-is-middleware/

[^7]: https://www.novasarc.com/middleware-types-powering-modern-applications

[^8]: https://bb.com.tr/en/blog/software-development/microservices-and-middleware-a-detailed-overview

[^9]: https://www.slashid.dev/blog/auth-patterns/

[^10]: https://www.snaplogic.com/blog/what-is-middleware

[^11]: https://forbytes.com/blog/integration-middleware/

[^12]: https://moldstud.com/articles/p-best-practices-for-using-middleware-in-aspnet-core-applications-enhance-performance-scalability

[^13]: https://nhimg.org/community/non-human-identity-management-general-discussions/understanding-backend-authentication-and-authorization-patterns/

[^14]: https://startup-house.com/glossary/what-is-middleware-patterns

[^15]: https://itnext.io/middleware-eb7302799596

[^16]: https://dev.to/somadevtoo/19-microservices-patterns-for-system-design-interviews-3o39

[^17]: https://loadforge.com/guides/optimizing-middleware-for-improved-expressjs-performance

[^18]: https://dev.to/wallacefreitas/performance-optimization-with-middleware-in-nodejs-3c19

[^19]: https://learn.microsoft.com/en-us/aspnet/core/fundamentals/middleware/?view=aspnetcore-9.0

[^20]: https://workos.com/blog/auth-in-middleware-or-how-i-learned-to-stop-worrying-and-love-the-edge

[^21]: https://saasykit.com/blog/12-top-security-best-practices-for-your-laravel-application

[^22]: https://www.linkedin.com/advice/0/how-do-you-optimize-middleware-performance

[^23]: https://magecomp.com/blog/what-is-middleware-and-how-does-it-work-in-laravel-8/

[^24]: https://laravel.com/docs/12.x/middleware

[^25]: https://laravel-docs-pt-br.readthedocs.io/en/latest/middleware/

[^26]: https://yellowduck.be/posts/exploring-laravel-middleware-post-response-actions

[^27]: https://skillions.in/custom-middleware-groups-in-laravel/

[^28]: https://techsolutionstuff.com/post/secure-your-laravel-12-login-with-throttle-middleware

[^29]: https://www.vfixtechnology.com/how-to-secure-laravel-login-with-throttle-middleware-in-just-2-easy-steps

[^30]: https://jordandalton.com/articles/advanced-laravel-terminable-middleware

[^31]: https://saravanasai.hashnode.dev/asynchronous-tasks-made-easy-with-laravel-terminable-middleware

[^32]: https://laravel-news.com/configuring-middleware-in-laravel

[^33]: https://stackoverflow.com/questions/35439234/laravel-dependency-injection-in-middleware

[^34]: https://www.interserver.net/tips/kb/mastering-laravels-service-container-for-dependency-injection/

[^35]: https://laravel.com/docs/12.x/container

[^36]: https://semaphore.io/community/tutorials/testing-middleware-in-laravel-with-phpunit

[^37]: https://matthewdaly.co.uk/blog/2016/11/29/testing-laravel-middleware/

[^38]: https://www.kai-sassnowski.com/post/testing-http-middleware-in-laravel/

[^39]: https://apisyouwonthate.com/blog/testing-http-middleware-in-laravel/

[^40]: https://dev.to/sergunik/a-guide-to-testing-middleware-in-laravel-1jna

[^41]: https://www.ebsco.com/research-starters/computer-science/middleware

[^42]: https://dev.to/hexa-home/understanding-middleware-in-web-development-the-essential-connector-2i38

[^43]: https://lig-membres.imag.fr/krakowia/Files/MW-Book/Chapters/Basic/main.pdf

[^44]: https://www.alibabacloud.com/en/knowledge/tech/what-is-middleware-in-web-development?_p_lc=1

[^45]: https://repositorio.pucrs.br/dspace/bitstream/10923/15213/2/A_Design_Patterns_Based_Middleware_for_Multiprocessor_Systems_on_Chip.pdf

[^46]: https://www.patterns.dev/vanilla/mediator-pattern/

[^47]: https://lig-membres.imag.fr/krakowia/Files/MW-Book/index.html

[^48]: https://www.baeldung.com/cs/middleware

[^49]: https://avadasoftware.com/designing-a-resilient-middleware-architecture-best-practices-for-modern-enterprises/

[^50]: https://aws.amazon.com/what-is/middleware/

[^51]: https://dev.to/hoangit/complete-explanation-with-example-on-laravel-middleware-26bi

[^52]: https://laraveldaily.com/post/middleware-laravel-main-things-to-know

[^53]: https://dev.to/mohammad_naim_443ffb5d105/understanding-laravel-middleware-a-deep-dive-into-laravel-11s-new-approach-3gnb

[^54]: https://benjamincrozat.com/customize-middleware-laravel-11

[^55]: https://www.codemag.com/Article/2301041/Mastering-Routing-and-Middleware-in-PHP-Laravel

[^56]: https://laravel.com/docs/9.x/middleware

[^57]: https://neon.com/guides/laravel-routes-middleware-validation

[^58]: https://itsolutionstuff.com/post/laravel-10-middleware-tutorial-exampleexample.html

[^59]: https://inspector.dev/how-to-create-custom-middleware-in-laravel/

[^60]: https://laravel.com/docs/5.0/middleware

[^61]: https://alemsbaja.hashnode.dev/how-to-create-and-register-a-middleware-in-a-laravel-11-application

[^62]: https://alishoff.com/blog/434

[^63]: https://backpackforlaravel.com/articles/tutorials/what-can-you-do-with-laravel-middleware-more-than-you-think

[^64]: https://www.luckymedia.dev/blog/laravel-for-beginners-middleware

[^65]: https://www.youtube.com/watch?v=Gr1Mmb1KYA8

[^66]: https://stackoverflow.com/questions/70169865/throttle-middleware-is-not-working-for-unauthenticated-users-when-used-with-auth

[^67]: https://www.youtube.com/watch?v=wmM9CFFpy34

[^68]: https://dev.to/cyber_aurora_/laravel-middleware-magic-use-cases-you-didnt-know-about-593e

[^69]: https://dev.to/eichgi/how-to-rate-limit-requests-in-laravel-with-throttlerequests-middleware-57f5

[^70]: https://stackoverflow.com/questions/50427439/laravel-add-custom-middleware-to-route-group

[^71]: https://muneebdev.com/practical-example-laravel-rate-limiting-for-api-endpoints/

[^72]: https://laraveldaily.com/lesson/laravel-from-scratch/admin-users-route-groups-middleware

[^73]: https://www.fastcomet.com/tutorials/laravel/middleware

[^74]: https://stackoverflow.com/questions/45180537/laravel-integrating-throttle-in-custom-login

[^75]: https://laravel.com/docs/12.x/authentication

[^76]: https://www.rishabhsoft.com/blog/enterprise-software-architecture-patterns

[^77]: https://www.openlegacy.com/blog/microservices-architecture-patterns/

[^78]: https://dzone.com/articles/database-performance-in-middleware-applications

[^79]: https://projectdiscovery.io/blog/nextjs-middleware-authorization-bypass

[^80]: https://middleware.io/blog/latency-reduction/

[^81]: https://www.oreilly.com/library/view/security-patterns-in/9781119970484/OEBPS/c05.htm

[^82]: https://middleware.io/blog/microservices-architecture/

[^83]: https://stackoverflow.blog/2021/10/06/best-practices-for-authentication-and-authorization-for-rest-apis/

[^84]: https://learn.microsoft.com/en-us/azure/architecture/guide/architecture-styles/microservices

[^85]: https://moldstud.com/articles/p-laravel-dependency-injection-common-use-cases-and-practical-examples

[^86]: https://github.com/laravel/framework/issues/44177

[^87]: https://tighten.com/insights/intro-to-terminable-middleware/

[^88]: https://laravel.com/docs/12.x/testing

[^89]: https://laracasts.com/discuss/channels/laravel/controller-dependency-is-injected-before-middleware-is-executed

