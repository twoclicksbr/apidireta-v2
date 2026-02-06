# ApiDireta
## Documento de Contexto do Projeto

## Visão Geral
O ApiDireta é uma ferramenta de criação de API RESTful disponível em apidireta.com. O usuário modela a estrutura do banco de dados de forma visual (analogia com phpMyAdmin) e o sistema gera automaticamente os endpoints da API, sem necessidade de codificação.

## Fluxo de Cadastro
No cadastro, o usuário informa: nome da empresa (tenant), plano escolhido (Go, Pro ou Max) com periodicidade mensal ou anual, dados pessoais (nome, data de nascimento, WhatsApp) armazenados na tabela persons, e dados de acesso (e-mail e senha) armazenados na tabela users.

## Analogia com phpMyAdmin
Projects = Banco de dados  |  Modules = Tabelas  |  Module_fields = Campos/Colunas

## Bancos de Dados por Projeto
Cada projeto gera 3 bancos de dados:
1. `apid_{tenant.slug}_{project.slug}_sand` — Sandbox (testes/desenvolvimento)
2. `apid_{tenant.slug}_{project.slug}_prod` — Produção (ambiente real)
3. `apid_{tenant.slug}_{project.slug}_log` — Logs (com campo environment: sand/prod)

## Endpoints por Módulo
Cada módulo gera automaticamente 7 endpoints: Index (GET), Show (GET), Store (POST), Update (PUT/PATCH), Soft Delete (DELETE), Restore (PATCH), Report (GET). O controle de ativo/inativo é feito por checkboxes (has_*) na tabela modules. A documentação de cada endpoint fica na tabela module_endpoints.

## Model Genérica Dinâmica
Antes de executar qualquer operação, a model genérica consulta a module_fields, interpreta os dados (tipos, hidden, relacionamentos) e monta dinamicamente: $fillable, $hidden, $casts e os relacionamentos (belongsTo, hasMany). O campo type=password aplica hash e hidden automaticamente.

## Regras de Negócio
- **Unicidade dos campos** é controlada pela aplicação, não refletida no banco do tenant.
- **Relacionamentos** (FK via related_module_id e related_module_field_id) são refletidos no banco do tenant.
- **Na criação do módulo**, 5 campos são gerados automaticamente: id (ordem 1), order (997), created_at (998), updated_at (999) e deleted_at (1000).
- **Na criação do módulo**, 7 registros de module_endpoints são gerados automaticamente.
- Se **is_current_timestamp=true**, o campo default_value é ignorado.
- O **acesso ao projeto** é controlado por tokens: sand_token e prod_token.
- Campos com **type=password** recebem hash (bcrypt) e são hidden automaticamente.

## Restrição de Acesso
Duas telas de permissão:

**Tela 1 — Permissões do sistema (person_system_permissions):** Controla acesso aos módulos internos do ApiDireta (tenants, persons, users, projects, modules, module_fields). O campo system_module é uma string de referência, permitindo adicionar novos módulos sem alterar a estrutura do banco.

**Tela 2 — Permissões dos endpoints (person_permissions):** Controla quais endpoints cada pessoa pode acessar nos módulos gerados pelo cliente (index, show, store, update, soft_delete, restore, report).

## Evolução Futura
Planejada a criação da tabela module_field_ui para configurações de front-end (label, placeholder, input_type, mask, min, max, tooltip), possibilitando a construção de interfaces visuais a partir dos módulos.

---

## Modelagem das Tabelas

### 1. tenants
Armazena as empresas (inquilinos) cadastradas na plataforma.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| name | VARCHAR | NOT NULL | Nome da empresa |
| slug | VARCHAR | UNIQUE | Slug único do tenant |
| expires_at | TIMESTAMP | NULLABLE | Data de expiração do tenant |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 2. plans
Planos disponíveis na plataforma: Go, Pro e Max.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| name | VARCHAR | NOT NULL | Go, Pro ou Max |
| slug | VARCHAR | NOT NULL | Slug do plano |
| monthly_price | DECIMAL | NOT NULL | Preço mensal |
| annual_price | DECIMAL | NOT NULL | Preço anual |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

### 3. plan_features
Funcionalidades e recursos de cada plano.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| plan_id | BIGINT | FK → plans | Plano relacionado |
| feature | VARCHAR | NOT NULL | Nome da funcionalidade |
| value | VARCHAR | NOT NULL | Valor da funcionalidade |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

### 4. persons
Dados pessoais dos usuários, vinculados ao tenant.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| tenant_id | BIGINT | FK → tenants | Tenant relacionado |
| name | VARCHAR | NOT NULL | Nome completo |
| birth_date | DATE | NOT NULL | Data de nascimento |
| whatsapp | VARCHAR | NOT NULL | Número de WhatsApp |
| status | VARCHAR | NOT NULL | Status da pessoa |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 5. users
Dados de autenticação dos usuários.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| person_id | BIGINT | FK → persons | Pessoa relacionada |
| email | VARCHAR | UNIQUE | E-mail de acesso |
| password | VARCHAR | NOT NULL | Senha (hash) |
| status | VARCHAR | NOT NULL | Status do usuário |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 6. tenant_plans
Relacionamento entre tenants e planos contratados.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| tenant_id | BIGINT | FK → tenants | Tenant relacionado |
| plan_id | BIGINT | FK → plans | Plano contratado |
| billing_cycle | VARCHAR | NOT NULL | mensal ou anual |
| starts_at | TIMESTAMP | NOT NULL | Início do plano |
| ends_at | TIMESTAMP | NOT NULL | Fim do plano |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

### 7. projects
Projetos de API criados pelo tenant. Equivale a um banco de dados no phpMyAdmin. Cada projeto gera 3 bancos: _sand (sandbox), _prod (produção) e _log (logs).

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| tenant_id | BIGINT | FK → tenants | Tenant relacionado |
| name | VARCHAR | UNIQUE/tenant | Nome único por tenant |
| slug | VARCHAR | UNIQUE/tenant | Slug único por tenant |
| db_name_sand | VARCHAR | NOT NULL | Nome do banco sandbox |
| db_user_sand | VARCHAR | NOT NULL | Usuário do banco sandbox |
| db_password_sand | VARCHAR | NOT NULL | Senha do banco sandbox |
| db_name_prod | VARCHAR | NOT NULL | Nome do banco produção |
| db_user_prod | VARCHAR | NOT NULL | Usuário do banco produção |
| db_password_prod | VARCHAR | NOT NULL | Senha do banco produção |
| db_name_log | VARCHAR | NOT NULL | Nome do banco log |
| db_user_log | VARCHAR | NOT NULL | Usuário do banco log |
| db_password_log | VARCHAR | NOT NULL | Senha do banco log |
| sand_token | VARCHAR | NOT NULL | Token de acesso sandbox |
| prod_token | VARCHAR | NOT NULL | Token de acesso produção |
| status | VARCHAR | NOT NULL | Status do projeto |
| order | INTEGER | NOT NULL | Ordem de exibição |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 8. modules
Módulos (entidades) dentro de cada projeto. Equivale a tabelas no phpMyAdmin. Na criação, gera automaticamente 5 campos padrão e 7 registros de module_endpoints.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| project_id | BIGINT | FK → projects | Projeto relacionado |
| name | VARCHAR | UNIQUE/project | Nome único por projeto |
| slug | VARCHAR | UNIQUE/project | Slug único por projeto |
| has_index | BOOLEAN | NOT NULL | Endpoint Index ativo |
| has_show | BOOLEAN | NOT NULL | Endpoint Show ativo |
| has_store | BOOLEAN | NOT NULL | Endpoint Store ativo |
| has_update | BOOLEAN | NOT NULL | Endpoint Update ativo |
| has_soft_delete | BOOLEAN | NOT NULL | Endpoint Soft Delete ativo |
| has_restore | BOOLEAN | NOT NULL | Endpoint Restore ativo |
| has_report | BOOLEAN | NOT NULL | Endpoint Report ativo |
| status | VARCHAR | NOT NULL | Status do módulo |
| order | INTEGER | NOT NULL | Ordem de exibição |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 9. module_fields
Campos de cada módulo. Equivale a colunas no phpMyAdmin. Na criação do módulo, 5 campos são gerados automaticamente: id (ordem 1), order (997), created_at (998), updated_at (999) e deleted_at (1000).

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| module_id | BIGINT | FK → modules | Módulo relacionado |
| name | VARCHAR | UNIQUE/module | Nome único por módulo |
| slug | VARCHAR | UNIQUE/module | Slug único por módulo |
| type | VARCHAR | NOT NULL | string, integer, text, boolean, date, password, etc. |
| is_unsigned | BOOLEAN | NOT NULL | Atributo UNSIGNED |
| is_current_timestamp | BOOLEAN | NOT NULL | CURRENT_TIMESTAMP (ignora default_value) |
| is_hidden | BOOLEAN | NOT NULL | Oculto nas respostas da API |
| is_unique | BOOLEAN | NOT NULL | Unicidade (controlada pela aplicação) |
| is_nullable | BOOLEAN | NOT NULL | Permite valor nulo |
| is_required | BOOLEAN | NOT NULL | Campo obrigatório |
| default_value | VARCHAR | NULLABLE | Valor padrão do campo |
| related_module_id | BIGINT | FK → modules | Módulo de relacionamento (nullable) |
| related_module_field_id | BIGINT | FK → module_fields | Campo chave do relacionamento (nullable) |
| status | VARCHAR | NOT NULL | Status do campo |
| order | INTEGER | NOT NULL | Ordem de exibição |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 10. module_endpoints
Documentação dos endpoints de cada módulo. Os 7 registros são criados automaticamente na criação do módulo. O controle de ativo/inativo é feito pelos campos has_* na tabela modules.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| module_id | BIGINT | FK → modules | Módulo relacionado |
| type | VARCHAR | NOT NULL | index, show, store, update, soft_delete, restore, report |
| description | TEXT | NULLABLE | Descrição do que o endpoint faz |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

### 11. person_permissions
Permissões de cada pessoa sobre os endpoints dos módulos do cliente.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| person_id | BIGINT | FK → persons | Pessoa relacionada |
| module_id | BIGINT | FK → modules | Módulo relacionado |
| can_index | BOOLEAN | NOT NULL | Pode listar registros |
| can_show | BOOLEAN | NOT NULL | Pode ver registro |
| can_store | BOOLEAN | NOT NULL | Pode criar registro |
| can_update | BOOLEAN | NOT NULL | Pode atualizar registro |
| can_soft_delete | BOOLEAN | NOT NULL | Pode excluir registro |
| can_restore | BOOLEAN | NOT NULL | Pode restaurar registro |
| can_report | BOOLEAN | NOT NULL | Pode gerar relatório |
| status | VARCHAR | NOT NULL | Status da permissão |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |

### 12. person_system_permissions
Permissões de cada pessoa sobre os módulos internos do ApiDireta (tenants, persons, users, projects, etc.). O system_module é uma string de referência. Para novos módulos internos, basta adicionar registros.

| Campo | Tipo | Regras | Observações |
|-------|------|--------|-------------|
| id | BIGINT | PK, AI | Identificador único |
| person_id | BIGINT | FK → persons | Pessoa relacionada |
| system_module | VARCHAR | NOT NULL | tenants, persons, users, projects, modules, module_fields, etc. |
| can_index | BOOLEAN | NOT NULL | Pode listar |
| can_show | BOOLEAN | NOT NULL | Pode ver |
| can_store | BOOLEAN | NOT NULL | Pode criar |
| can_update | BOOLEAN | NOT NULL | Pode atualizar |
| can_soft_delete | BOOLEAN | NOT NULL | Pode excluir |
| can_restore | BOOLEAN | NOT NULL | Pode restaurar |
| status | VARCHAR | NOT NULL | Status da permissão |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete |
