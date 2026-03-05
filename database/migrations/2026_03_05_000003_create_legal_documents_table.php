<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('title', 160);
            $table->longText('content');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('legal_documents')->insert([
            [
                'key' => 'privacy',
                'title' => 'Política de Privacidade',
                'content' => implode("\n\n", [
                    'Última atualização: '.$now->format('d/m/Y'),
                    'A MedidaTek respeita a sua privacidade e trata dados pessoais de forma responsável, seguindo princípios da LGPD (Lei nº 13.709/2018). Esta Política descreve como coletamos, usamos, armazenamos e protegemos dados quando você acessa nosso site e utiliza nossos formulários.',
                    '1. Quem somos',
                    'MedidaTek ("nós"). Este site é um canal institucional e de captação de oportunidades. Para exercer seus direitos ou tirar dúvidas, utilize o formulário de contato do site.',
                    '2. Quais dados coletamos',
                    'Podemos coletar, conforme você interage com o site:',
                    '- Dados fornecidos por você: nome, e-mail, WhatsApp, empresa, mensagem e informações sobre o projeto.',
                    '- Dados de navegação e contexto: origem de campanha (UTMs), caminho visitado, referência (referrer), data e hora de envio.',
                    '- Dados técnicos mínimos: para fins de segurança e prevenção a abuso, o servidor pode registrar identificadores técnicos (ex.: endereço IP e user-agent) conforme as configurações do ambiente.',
                    '3. Finalidades de uso',
                    'Usamos os dados para:',
                    '- Responder contato e elaborar proposta/escopo.',
                    '- Compreender demanda e melhorar o site e as mensagens.',
                    '- Prevenir spam, abuso e fraudes.',
                    '4. Bases legais',
                    'Tratamos dados pessoais com base em uma ou mais bases legais aplicáveis, conforme o caso:',
                    '- Consentimento, quando você autoriza contato.',
                    '- Legítimo interesse, para responder solicitações, proteger o site e melhorar a experiência.',
                    '- Cumprimento de obrigação legal/regulatória, quando aplicável.',
                    '5. Compartilhamento',
                    'Podemos compartilhar dados com fornecedores estritamente necessários para operar o site e atender você (ex.: hospedagem, envio de e-mails, ferramentas de observabilidade), sempre que possível sob contratos e controles adequados. Não vendemos dados pessoais.',
                    '6. Retenção',
                    'Mantemos os dados pelo tempo necessário para atender as finalidades desta Política, inclusive para eventual defesa em processos, cumprimento de obrigações e histórico comercial. Quando não forem mais necessários, aplicaremos medidas razoáveis de exclusão ou anonimização.',
                    '7. Segurança',
                    'Aplicamos medidas técnicas e organizacionais razoáveis para proteger dados contra acesso não autorizado, perda, alteração ou destruição. Nenhum sistema é 100% seguro; por isso, não envie informações sensíveis (segredos, chaves, senhas, dados bancários) por formulários do site.',
                    '8. Seus direitos',
                    'Nos termos da LGPD, você pode solicitar confirmação de tratamento, acesso, correção, portabilidade (quando aplicável), anonimização/eliminação, informação sobre compartilhamentos e revogação de consentimento. Para solicitações, utilize o formulário de contato.',
                    '9. Cookies e tecnologias similares',
                    'Podemos utilizar cookies e recursos similares para funcionamento e métricas. Dependendo do seu navegador, você pode gerenciar cookies nas configurações.',
                    '10. Alterações',
                    'Podemos atualizar esta Política a qualquer momento. A versão vigente será sempre a publicada nesta página, com data de atualização.',
                ]),
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'terms',
                'title' => 'Termos de Uso',
                'content' => implode("\n\n", [
                    'Última atualização: '.$now->format('d/m/Y'),
                    'Ao acessar este site, você concorda com estes Termos de Uso. Se não concordar, interrompa a navegação.',
                    '1. Finalidade do site',
                    'Este site tem caráter institucional e informativo, além de permitir contato para avaliação de projetos. Não é um canal para suporte técnico de sistemas de terceiros.',
                    '2. Uso permitido',
                    'Você se compromete a:',
                    '- Não utilizar o site para práticas ilícitas, tentativa de invasão, exploração de falhas ou envio de spam.',
                    '- Fornecer informações verdadeiras e adequadas ao entrar em contato.',
                    '- Não enviar conteúdo confidencial, segredos industriais, credenciais, chaves de API, dados bancários ou informações sensíveis pelo formulário.',
                    '3. Propriedade intelectual',
                    'Textos, marcas, layout, identidade visual e demais conteúdos do site pertencem à MedidaTek ou são utilizados sob licença. É proibida a reprodução não autorizada.',
                    '4. Conteúdos e links externos',
                    'Podemos indicar links de terceiros. Não controlamos e não nos responsabilizamos por conteúdo, políticas ou práticas de sites externos.',
                    '5. Disponibilidade e garantia',
                    'O site é fornecido "como está" e pode ser alterado, suspenso ou descontinuado a qualquer momento. Não garantimos ausência de falhas, indisponibilidade ou que o conteúdo esteja sempre atualizado.',
                    '6. Limitação de responsabilidade',
                    'Na máxima extensão permitida por lei, a MedidaTek não se responsabiliza por danos indiretos, lucros cessantes, perda de dados, interrupções ou qualquer dano decorrente do uso ou incapacidade de uso do site.',
                    '7. Propostas e contratação',
                    'Qualquer conversa iniciada via formulário não constitui obrigação de contratação. Condições comerciais, prazos, escopo, propriedade intelectual e responsabilidades somente serão válidos mediante contrato específico.',
                    '8. Privacidade',
                    'O tratamento de dados pessoais é regido pela nossa Política de Privacidade.',
                    '9. Alterações',
                    'Podemos atualizar estes Termos a qualquer momento. A versão vigente será a publicada nesta página, com data de atualização.',
                ]),
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};

