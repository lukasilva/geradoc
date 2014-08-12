<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tipo extends CI_Controller {
	
	/*
	 * Atributos opcionais para as views
	* public $layout;  define o layout default
	* public $title; define o titulo default da view
	* public $css = array('css1','css2'); define os arquivos css default
	* public $js = array('js1','js2'); define os arquivos javascript default
	* public $images = 'dir_images'; define a diretório default das imagens
	*
	*/
	
	public $layout = 'default';
	public $css = array('style','demo_page','demo_table_jui','jquery-ui-1.8.11.custom');
	public $js = array('jquery-1.7.1.min','jquery.dataTables.min','jquery.blockUI','about');
	public $js_custom;
	
    private $area = "tipo";
        
	public function __construct (){
		parent::__construct();	
		$this->load->library(array('restrict_page','table','form_validation','session'));
		$this->load->helper('url');
		$this->load->model('Tipo_model','',TRUE);
        $this->load->model('Grid_model','',TRUE);
        $this->modal = $this->load->view('about_modal', '', TRUE);
        session_start();
	}

	public function index(){
		
		$this->js[] = 'tipo';
		
		$data['titulo']     = 'Tipos';
		$data['link_add']   = anchor($this->area.'/add/','Adicionar',array('class'=>'add'));
		$data['link_back']  = anchor('documento/index/','Lista de Documentos',array('class'=>'back'));
		$data['form_action'] = site_url($this->area.'/search');
		
		//------- BUSCA -------//
		$data['keyword_'.$this->area] = '';
		if(isset($_SESSION['keyword_'.$this->area]) == true and $_SESSION['keyword_'.$this->area] != null){
			$data['keyword_'.$this->area] = $_SESSION['keyword_'.$this->area];
			redirect($this->area.'/search/');
		}else{
			$data['keyword_'.$this->area] = 'pesquisa textual';
			$data['link_search_cancel'] = '';
		}
		//--- FIM DA BUSCA ---//
		
		//Inicio da Paginacao
        $this->load->library('pagination');
        $maximo = 10;
        $uri_segment = 3;
        $inicio = (!$this->uri->segment($uri_segment, 0)) ? 0 : ($this->uri->segment($uri_segment, 0) - 1) * $maximo;
        $_SESSION['novoinicio'] = $this->uri->segment($uri_segment - 1, 'index').'/'.$this->uri->segment($uri_segment, 0);  //cria uma variavel de sessao para retornar a pagina correta apos visualizacao, delecao ou alteracao
        $config['base_url'] = site_url($this->area.'/index/');
        $config['total_rows'] = $this->Tipo_model->count_all();
        $config['per_page'] = $maximo;

        $this->pagination->initialize($config);

        $objetos = $this->Tipo_model->get_paged_list($maximo, $inicio);
        
        // carregando os dados na tabela
        $this->load->library('table');
        $this->table->set_empty("&nbsp;");
        $this->table->set_heading('Item', 'Nome', 'Ações');
        foreach ($objetos as $objeto){
        	
        	if($objeto->publicado == 'N'){
        		
        	
            $this->table->add_row($objeto->id, $objeto->nome,
                anchor($this->area.'/view/'.$objeto->id,'visualizar',array('class'=>'view')).' '.
                anchor($this->area.'/update/'.$objeto->id,'alterar',array('class'=>'update')).' '.
            	anchor($this->area.'/year/'.$objeto->id,'ano',array('class'=>'calendar')).' '.
            	anchor($this->area.'/altera_publicacao/'.$objeto->id,'despublicado',array('class'=>'no_ok'))
              //  anchor($this->area.'/delete/'.$objeto->id,'deletar',array('class'=>'delete','onclick'=>"return confirm('Deseja REALMENTE deletar esse orgao?')"))
            );
            
        	}else{
        		$this->table->add_row($objeto->id, $objeto->nome,
        				anchor($this->area.'/view/'.$objeto->id,'visualizar',array('class'=>'view')).' '.
        				anchor($this->area.'/update/'.$objeto->id,'alterar',array('class'=>'update')).' '.
        				anchor($this->area.'/year/'.$objeto->id,'ano',array('class'=>'calendar')).' '.
        				anchor($this->area.'/altera_publicacao/'.$objeto->id,'publicado',array('class'=>'ok'))
        				//  anchor($this->area.'/delete/'.$objeto->id,'deletar',array('class'=>'delete','onclick'=>"return confirm('Deseja REALMENTE deletar esse orgao?')"))
        		);
        		
        	}
        }

        //Monta a DataTable
        $tmpl = $this->Grid_model->monta_tabela_list();
        $this->table->set_template($tmpl);
        // Fim da DataTable

        $data['table']		= $this->table->generate();
        $data["total_rows"] = $config['total_rows'];
        $data['pagination'] = $this->pagination->create_links();

        $this->load->view($this->area.'/'.$this->area.'_list', $data);

	}
	
	public function add() {
	
		$this->load->library(array('form_validation'));
		$this->form_validation->set_error_delimiters('<div class="error_field"> <img class="img_align" src="{TPL_images}/error.png" alt="! " /> ', '</div>');
	
		$data['titulo'] = 'Novo Tipo';
		$data['link_back']  = anchor($this->area.'/index/','Voltar',array('class'=>'back'));
		$data['form_action'] = site_url($this->area.'/add/');
		$data['mensagem'] = '';
	
		//constroe os campos que serao mostrados no formulario
		$this->load->model('Campo_model','',TRUE);
		
		$data['campoNome'] = $this->Campo_model->tipo('campoNome');
		$data['campoAbreviacao'] = $this->Campo_model->tipo('campoAbreviacao');
		$data['campoCabecalho'] = $this->Campo_model->tipo('campoCabecalho');
		$data['campoConteudo'] = $this->Campo_model->tipo('campoConteudo');
		$data['campoRodape'] = $this->Campo_model->tipo('campoRodape');
		
	
		if ($this->form_validation->run($this->area."/add") == FALSE) {
			$this->load->view($this->area . "/" . $this->area.'_edit', $data);
		} else {
			//cria o objeto com os dados passados via post
			$objeto_do_form = array(
					
					'nome' => mb_convert_case($this->input->post('campoNome'), MB_CASE_UPPER, "UTF-8"),
					'abreviacao' => mb_convert_case($this->input->post('campoAbreviacao'), MB_CASE_UPPER, "UTF-8"),
					'layout' => $this->input->post('campoConteudo'),
					'cabecalho' => $this->input->post('campoCabecalho'),
					'rodape' => $this->input->post('campoRodape')
					
			);
			
			// corrige o caminho do local das imagens enviadas
			$objeto_do_form['layout'] = str_replace('../../', '../../../', $objeto_do_form['layout']);
			$objeto_do_form['cabecalho'] = str_replace('../../', '../../../', $objeto_do_form['cabecalho']);
			$objeto_do_form['rodape'] = str_replace('../../', '../../../', $objeto_do_form['rodape']);
	
			//checa a existencia de registro com o mesmo nome para evitar duplicatas
			$checa_duplicata = $this->Tipo_model->get_by_nome($objeto_do_form['nome'])->num_rows();
	
			if ($checa_duplicata > 0){
	
				$data['mensagem'] = '<div class="error_field" style="text-align: center;"> <img class="img_align" src="{TPL_images}/error.png" alt="! " /> O registro já existe </div>';
	
				$this->load->view($this->area . "/" . $this->area.'_edit', $data);
	
			}else{
	
				// Salva o registro
				$this->Tipo_model->save($objeto_do_form);
	
				$this->js_custom = 'var sSecs = 4;
                                function getSecs(){
                                    sSecs--;
                                    if(sSecs<0){ sSecs=59; sMins--; }
                                    $("#clock1").html(sSecs+" segundos.");
                                    setTimeout("getSecs()",1000);
                                    var s =  $("#clock1").html();
                                    if (s == "1 segundos."){
                                        window.location.href = "' . site_url('/'.$this->area) . '";
                                    }
                                }
                                ';
	
				$data['mensagem'] = "<br /> Redirecionando em ";
				$data['mensagem'] .= '<span id="clock1"> ' . "<script>setTimeout('getSecs()',1000);</script> </span>";
				$data['link1'] = '';
				$data['link2'] = '';
	
				$this->load->view('success', $data);
	
			}
	
		}
	
	}
	
	function view($id){

		$data['titulo'] = 'Detalhes do tipo de documento';
		
        $data['message'] = '';
        
		$data['link_back'] = anchor($this->area.'/'.$_SESSION['novoinicio'],'Voltar',array('class'=>'back'));
		
		$data['objeto'] = $this->Tipo_model->get_by_id($id)->row();

		$this->load->view($this->area.'/'.$this->area.'_view', $data);

	}
	
	function get_tipo($id){
	
		$tipo = $this->Tipo_model->get_by_id($id)->row();
	
	}
	
public function update($id) {

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="error_field"> <img class="img_align" src="{TPL_images}/error.png" alt="! " /> ', '</div>');
			
		// define as variaveis comuns
		$data['titulo'] = "Alteração de  órgão";
		$data['mensagem'] = '';
		$data['link_back'] = anchor($this->area.'/'.$_SESSION['novoinicio'],'Voltar',array('class'=>'back'));
		$data['form_action'] = site_url($this->area.'/update/'.$id);

		//Constroe os campos do formulario
		$this->load->model('Campo_model','',TRUE);
		
		$data['campoNome'] = $this->Campo_model->tipo('campoNome');
		$data['campoAbreviacao'] = $this->Campo_model->tipo('campoAbreviacao');
		$data['campoCabecalho'] = $this->Campo_model->tipo('campoCabecalho');
		$data['campoConteudo'] = $this->Campo_model->tipo('campoConteudo');
		$data['campoRodape'] = $this->Campo_model->tipo('campoRodape');
		
		
			
		// Instancia um objeto com o resultado da consulta
		$obj = $this->Tipo_model->get_by_id($id)->row();

		//Popula os campos com os dados do objeto
		$data['campoNome']['value'] = $obj->nome;
		$data['campoAbreviacao']['value'] = $obj->abreviacao;
		$data['campoConteudo']['value'] = $obj->layout;
		$data['campoCabecalho']['value'] = $obj->cabecalho;
		$data['campoRodape']['value'] = $obj->rodape;
		

		if ($this->form_validation->run($this->area."/add") == FALSE) {

			$this->load->view($this->area.'/'.$this->area.'_edit', $data);
				
		} else {

			//cria um objeto com os dados passados via post
			$objeto_do_form = array(
					
               		'nome' => mb_convert_case($this->input->post('campoNome'), MB_CASE_UPPER, "UTF-8"),
					'abreviacao' => mb_convert_case($this->input->post('campoAbreviacao'), MB_CASE_UPPER, "UTF-8"),
					'layout' => $this->input->post('campoConteudo'),
					'cabecalho' => $this->input->post('campoCabecalho'),
					'rodape' => $this->input->post('campoRodape')
					
			);

			//trata os campos necessarios

			// $objeto_do_form['data_nascimento'] = $this->_trata_dataDoFormParaBanco($objeto_do_form['data_nascimento']);
			// $objeto_do_form['cpf'] = $this->_trata_CPFdoFormParaBanco($objeto_do_form['cpf']);

			// Checa duplicata
			$checa_duplicata = $this->Tipo_model->get_by_nome($objeto_do_form['nome'])->num_rows();

			if ($checa_duplicata > 1){

				$data['mensagem'] = '<div class="error_field"> <img class="img_align" src="{TPL_images}/error.png" alt="! " /> O registro já existe </div>';

				$this->load->view($this->area.'/'.$this->area.'_edit', $data);

			}else{

				// Atualiza o cadastro
				$this->Tipo_model->update($id, $objeto_do_form);

				$this->js_custom = 'var sSecs = 4;
                                function getSecs(){
                                    sSecs--;
                                    if(sSecs<0){ sSecs=59; sMins--; }				
                                    $("#clock1").html(sSecs+" segundos");		
                                    setTimeout("getSecs()",1000);		
                                    var s =  $("#clock1").html();
                                    if (s == "1 segundos"){			
                                        window.location.href = "' . site_url($this->area.'/'.$_SESSION['novoinicio']) . '";
                                    }
                                }     		
                                ';

				$data['mensagem'] = "<br /><br />Redirecionando em... ";
				$data['mensagem'] .= '<span id="clock1"> ' . "<script>setTimeout('getSecs()',1000);</script> </span>";
				$data['link1'] = '';
				$data['link2'] = '';

				$this->load->view('success', $data);
					
			}

		}
	}
	
	function delete($id){
		// delete orgao
		$this->Tipo_model->delete($id);
		
		// redirect to orgao list page
		redirect('orgao/index/'.$_SESSION['novoinicio']);
	}
	
/*
|--------------------------------------------------------------------------
| Metodos relacionados ao ano de vigencia do tipo de documento
|--------------------------------------------------------------------------
*/
	
	function year($id){
	
		$data['titulo'] = 'Vigência do tipo de documento';
		$data['message'] = (isset($_SESSION['message']) and $_SESSION['message'] != null) ? $_SESSION['message'] : null;
		$_SESSION['message'] = null;
		$data['link_back'] = anchor($this->area.'/'.$_SESSION['novoinicio'],'Voltar',array('class'=>'back'));
		$data['form_action'] = site_url($this->area.'/year/'.$id);
		$this->form_validation->set_error_delimiters('<span class="error_field"> <img class="img_align" src="{TPL_images}/error.png" alt="!" /> ', '</span>');
	
	
		//Constroe os campos do formulario
		$this->load->model('Campo_model','',TRUE);
		$data['campoAno'] = $this->Campo_model->tipo('campoAno');
		$data['campoInicio'] = $this->Campo_model->tipo('campoInicio');
	
		$data['objeto'] = $this->Tipo_model->get_by_id($id)->row();
	
		//Popula os campos com os dados do objeto
		$data['campoAno']['value'] = $data['objeto']->ano;
		$data['campoInicio']['value'] = $data['objeto']->inicio;
	
	
		//---------- FUNCIONÁRIOS ----------//
	
		$years = $this->Tipo_model->list_years($id)->result();
	
		$linha = "";
		if($years){
	
			$linha = "<table>\n";
			$linha .= "<tr>\n";
			$linha .= "<td style='text-align:center;'>\n";
			$linha .=  "Ano";
			$linha .= "</td>\n";
			$linha .= "<td style='text-align:center;'>\n";
			$linha .=  "Início da contagem";
			$linha .= "</td>\n";
			$linha .= "<td style='text-align:center;'>\n";
			$linha .=  "Ações";
			$linha .= "</td>\n";
			$linha .= "</tr>\n";
				
			foreach ($years as $key => $year){
	
				$linha .= "<tr>\n";
	
				$linha .= "<td style='text-align:center;'>\n";
	
				$linha .=  $year->ano;
	
				$linha .= "</td>\n";
	
				$linha .= "<td style='text-align:center;'>\n";
	
				$linha .=  $year->inicio;
					
				$linha .= "</td>\n";
					
				$linha .= "<td style='text-align:center;'>\n";
					
				$linha .=  anchor($this->area.'/update_year/'.$year->id,'alterar',array('class'=>'update')) ." ". anchor($this->area.'/delete_year/'.$year->id,'deletar',array('class'=>'delete','onclick'=>"return confirm('Deseja REALMENTE deletar esse registro?')"));
	
				$linha .= "</td>\n";
	
				$linha .= "</tr>\n";
	
			}
			$linha .= "</table>\n";
		}else{
			$arrayYars[1] = "";
		}
		$data['years']  =  $linha;
	
	
		//---------- FIM ------------------//
	
	
		if ($this->form_validation->run($this->area."/year") == FALSE) {
	
			$this->load->view($this->area.'/'.$this->area.'_ano', $data);
	
		} else {
	
			//cria um objeto com os dados passados via post
			$objeto_do_form = array(
					'tipo' => $id,
					'ano' => $this->input->post('campoAno'),
					'inicio' => $this->input->post('campoInicio'),
			);
	
			// Atualiza o cadastro
			$this->Tipo_model->set_year($objeto_do_form);
	
			redirect($this->area.'/year/'.$id);
	
			/*
			 $this->js_custom = 'var sSecs = 4;
			function getSecs(){
			sSecs--;
			if(sSecs<0){ sSecs=59; sMins--; }
			$("#clock1").html(sSecs+" segundos");
			setTimeout("getSecs()",1000);
			var s =  $("#clock1").html();
			if (s == "1 segundos"){
			window.location.href = "' . site_url($this->area.'/'.$_SESSION['novoinicio']) . '";
			}
			}
			';
	
			$data['mensagem'] = "<br /><br />Redirecionando em... ";
			$data['mensagem'] .= '<span id="clock1"> ' . "<script>setTimeout('getSecs()',1000);</script> </span>";
			$data['link1'] = '';
			$data['link2'] = '';
	
			$this->load->view('success', $data);
			*/
	
	
		}
	
	}
	
	
	function update_year($id){
		
		$data['titulo'] = 'Edição da vigência do tipo de documento';
		$data['message'] = null;
		$data['link_back'] = anchor($this->area.'/'.$_SESSION['novoinicio'],'Voltar',array('class'=>'back'));
		$data['form_action'] = site_url($this->area.'/update_year/'.$id);
	
		//Constroe os campos do formulario
		$this->load->model('Campo_model','',TRUE);
		$data['campoAno'] = $this->Campo_model->tipo('campoAno');
		$data['campoInicio'] = $this->Campo_model->tipo('campoInicio');
	
		$data['objeto'] = $this->Tipo_model->get_year_by_id($id)->row();
		
		if ($this->em_uso($data['objeto']->tipo, $data['objeto']->ano) == true){
		
			$_SESSION['message']  = "<center><span class='error_field'><img class='img_align' src='{TPL_images}/error.png' alt='!' /> <br>";
			$_SESSION['message'] .= "Alteração inviável.<br>";
			$_SESSION['message'] .= "Existem registros utilizando o ano de <b>".$data['objeto']->ano."</b> para esse tipo de documento.";
			$_SESSION['message'] .= "<br></span></center>";
		
			redirect($this->area.'/year/'.$data['objeto']->tipo);
		}
		
		$data['objeto']->nome = $this->Tipo_model->get_by_id($data['objeto']->tipo)->row()->nome;
		$data['objeto']->abreviacao = $this->Tipo_model->get_by_id($data['objeto']->tipo)->row()->abreviacao;
	
		//Popula os campos com os dados do objeto
		$data['campoAno']['value'] = $data['objeto']->ano;
		$data['campoInicio']['value'] = $data['objeto']->inicio;
	
		if ($this->form_validation->run($this->area."/year_update") == FALSE) {
	
			$this->load->view($this->area.'/'.$this->area.'_ano_update', $data);
	
		} else {
	
			//cria um objeto com os dados passados via post
			$objeto_do_form = array(
					'ano' => $data['objeto']->ano,
					'inicio' => $this->input->post('campoInicio'),
			);
				
			// se a checagem retornar um valor diferente de nulo
			if ($this->em_uso($data['objeto']->tipo, $objeto_do_form['ano']) == true){
					
				$this->form_validation->set_error_delimiters('<span class="error_field"> <img class="img_align" src="{TPL_images}/error.png" alt="!" /> ', '</span>');
	
				$data['message']  = "<center><span class='error_field'> <img class='img_align' src='{TPL_images}/error.png' alt='!' /> <br> Já existe registro de ".$data['objeto']->nome." para o ano de ".$objeto_do_form['ano']."<br>";
				$data['message'] .= "Alteração inviável<br></span></center>";
	
				$this->load->view($this->area.'/'.$this->area.'_ano_update', $data);
					
			}else{
	
				// Atualiza o cadastro
				$this->Tipo_model->update_year($data['objeto']->id, $objeto_do_form);
	
				$this->js_custom = 'var sSecs = 4;
	                                function getSecs(){
	                                    sSecs--;
	                                    if(sSecs<0){ sSecs=59; sMins--; }
	                                    $("#clock1").html(sSecs+" segundos");
	                                    setTimeout("getSecs()",1000);
	                                    var s =  $("#clock1").html();
	                                    if (s == "1 segundos"){
	                                        window.location.href = "' . site_url($this->area.'/year/'.$data['objeto']->tipo) . '";
	                                    }
	                                }
	                                ';
	
				$data['mensagem'] = "<br /><br />Redirecionando em... ";
				$data['mensagem'] .= '<span id="clock1"> ' . "<script>setTimeout('getSecs()',1000);</script> </span>";
				$data['link1'] = '';
				$data['link2'] = '';
	
				$this->load->view('success', $data);
					
			}	
	
		}
	
	}
	
	function delete_year($id_year){

		$year = $this->Tipo_model->get_year_by_id($id_year)->row();
		
		if ($this->em_uso($year->tipo, $year->ano) == true){

			$_SESSION['message']  = "<center><span class='error_field'><img class='img_align' src='{TPL_images}/error.png' alt='!' /> <br>";
			$_SESSION['message'] .= "Exclusão inviável.<br>";
			$_SESSION['message'] .= "Existem registros utilizando o ano de <b>".$year->ano."</b> para esse tipo de documento.";
			$_SESSION['message'] .= "<br></span></center>";
		
			redirect($this->area.'/year/'.$year->tipo);
				
		}else{
			
			$this->Tipo_model->delete_year($id_year);
			redirect($this->area.'/year/'.$year->tipo);
		}
	}

    public function search($page = 1) { 
    	$this->js[] = 'tipo';
        $data['titulo'] = "Busca por tipos";
        $data['link_add']   = anchor($this->area.'/add/','Adicionar',array('class'=>'add'));
        $data['link_search_cancel'] = anchor($this->area.'/search_cancel/','CANCELAR PESQUISA',array('class'=>'button_cancel'));
        $data['form_action'] = site_url($this->area.'/search');

        $this->load->library(array('pagination', 'table'));
        
        if(isset($_SESSION['keyword_'.$this->area]) == true and $_SESSION['keyword_'.$this->area] != null and $this->input->post('search') == null){
        	$keyword = $_SESSION['keyword_'.$this->area];
        }else{
        	
        	$keyword = ($this->input->post('search') == null or $this->input->post('search') == "pesquisa textual") ? redirect($this->area.'/index/') : $this->input->post('search');
        	$_SESSION['keyword_'.$this->area] = $keyword;
        	
        }
        
        $maximo = 10;  
        $uri_segment = 3;  
        $_SESSION['novoinicio'] = $this->uri->segment($uri_segment - 1, 0).'/'.$this->uri->segment($uri_segment, 0); 
        $config['per_page'] = $maximo;    
        $config['base_url'] = site_url($this->area.'/search');
        $config['total_rows'] = $this->Tipo_model->count_all_search($keyword);           
        
        $this->pagination->initialize($config);     
        $data['pagination'] = $this->pagination->create_links();
        $this->table->set_empty("&nbsp;");
        $this->table->set_heading('Item', 'Nome','Ações');
        
        $inicio = (!$this->uri->segment($uri_segment, 0)) ? 0 : ($this->uri->segment($uri_segment, 0) - 1) * $maximo;

        $rows = $this->Tipo_model->listAllSearchPag($keyword, $maximo, $inicio);   
            
        foreach ($rows as $objeto){
        	
        	
        	if($objeto->publicado == 'N'){
        	
        		 
        		$this->table->add_row($objeto->id, $objeto->nome,
        				anchor($this->area.'/view/'.$objeto->id,'visualizar',array('class'=>'view')).' '.
        				anchor($this->area.'/update/'.$objeto->id,'alterar',array('class'=>'update')).' '.
        				anchor($this->area.'/year/'.$objeto->id,'ano',array('class'=>'calendar')).' '.
        				anchor($this->area.'/altera_publicacao/'.$objeto->id,'despublicado',array('class'=>'no_ok'))
        				//  anchor($this->area.'/delete/'.$objeto->id,'deletar',array('class'=>'delete','onclick'=>"return confirm('Deseja REALMENTE deletar esse orgao?')"))
        		);
        	
        	}else{
        		$this->table->add_row($objeto->id, $objeto->nome,
        				anchor($this->area.'/view/'.$objeto->id,'visualizar',array('class'=>'view')).' '.
        				anchor($this->area.'/update/'.$objeto->id,'alterar',array('class'=>'update')).' '.
        				anchor($this->area.'/year/'.$objeto->id,'ano',array('class'=>'calendar')).' '.
        				anchor($this->area.'/altera_publicacao/'.$objeto->id,'publicado',array('class'=>'ok'))
        				//  anchor($this->area.'/delete/'.$objeto->id,'deletar',array('class'=>'delete','onclick'=>"return confirm('Deseja REALMENTE deletar esse orgao?')"))
        		);
        	
        	}

        }
        
        //Monta a DataTable
        $tmpl = $this->Grid_model->monta_tabela_list();
        $this->table->set_template($tmpl);
        // Fim da DataTable

        $data['table'] = $this->table->generate();
        $data['total_rows'] = $config['total_rows'];
        $data['keyword_'.$this->area] = $keyword;    
                
        $this->load->view($this->area.'/'.$this->area.'_list', $data); 

    }
    
    public function search_cancel() {
    
    	$_SESSION['keyword_'.$this->area] = null;
    
    	redirect($this->area.'/index/');
    
    }
	
	// date_validation callback
	function valid_date($str){
		if(!preg_match('^(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-([0-9]{4})$^', $str))
		{
			$this->validation->set_message('valid_date', 'date format is not valid. dd-mm-yyyy');
			return false;
		}
		else
		{
			return true;
		}
	}
	
	function em_uso($tipo, $ano){
		
		$this->load->model('Documento_model','',TRUE);
		
		$checa_existencia = $this->Documento_model->get_by_tipo_e_ano($tipo, $ano)->row();
		
		
		if ($checa_existencia != null or $tipo == null or $tipo == 0 or $ano == null or $ano == 0){
			return true;
		}else{
			return false;
		}
	
	}
	
	function altera_publicacao($id){
	
		$obj = $this->Tipo_model->get_by_id($id)->row();
	
		if($obj->publicado == 'N'){
			$objeto_do_form = array(
					'publicado' => 'S',
			);
			$this->Tipo_model->update($id, $objeto_do_form);
			
		}elseif($obj->publicado == 'S'){
			$objeto_do_form = array(
					'publicado' => 'N',
			);
			$this->Tipo_model->update($id, $objeto_do_form);
		}
	
		// redirect to curso list page
		redirect($this->area.'/'.$_SESSION['novoinicio']);
	}
	
}
?>