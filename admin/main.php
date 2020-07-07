<?php
$maxupsize = str_replace('M','',ini_get('upload_max_filesize'));
$step = $step ? $step : 'step1';
$migset = array
(
	'member'=>'회원정보',
	'board'=>'게시물/댓글/첨부파일',
	'point'=>'포인트',
	'msg'=>'쪽지',
	'post'=>'포스트',
);
?>

<div id="migbox" class="p-4">
	<div class="notice">
		킴스큐 Rb1 또는 다른 프로그램의 데이터를 킴스큐 Rb2 데이터로 이전할 수 있습니다.<br>
		킴스큐-Rb2 용 마이그레이션 XML파일을 직접 등록하시거나 주소를 입력해 주세요..<br>
		마이그레이션 후 기존 첨부파일 폴더는 /files/ 폴더안에 업로드해주세요.<br>
		(업로드한 모든 폴더와 파일들은 퍼미션을 일괄적으로 707로 변경해주세요.)
	</div>

	<?php if($step == 'step1'):?>

	<form name="procForm" action="<?php echo $g['s']?>/" method="get">
		<input type="hidden" name="r" value="<?php echo $r?>">
		<input type="hidden" name="m" value="<?php echo $m?>">
		<input type="hidden" name="module" value="<?php echo $module?>">
		<input type="hidden" name="step" value="step2">

		<div class="mb-3">
			이전할 데이터를 선택해 주세요.
		</div>

		<div class="custom-control custom-radio mb-2">
		  <input type="radio" id="mig_member" name="migtype" value="member" class="custom-control-input" checked>
		  <label class="custom-control-label" for="mig_member">회원정보 데이터</label>
		</div>

		<div class="custom-control custom-radio mb-2">
			<input type="radio" id="mig_board" name="migtype" value="board" class="custom-control-input">
			<label class="custom-control-label" for="mig_board">게시물/댓글/첨부파일</label>
		</div>

		<div class="custom-control custom-radio mb-2">
			<input type="radio" id="mig_post" name="migtype" value="post" class="custom-control-input">
			<label class="custom-control-label" for="mig_post">포스트</label>
		</div>

		<div class="submitbox">
			<input type="submit" value=" 다음으로 " class="btn btn-light" />
		</div>

	</form>

	<?php endif?>

	<?php if($step == 'step2'):?>

	<form name="procForm" action="<?php echo $g['s']?>/" method="post" target="_action_frame_<?php echo $m?>" enctype="multipart/form-data" onsubmit="return saveCheck(this);">
		<input type="hidden" name="r" value="<?php echo $r?>" />
		<input type="hidden" name="m" value="<?php echo $module?>" />
		<input type="hidden" name="a" value="migration" />
		<input type="hidden" name="migtype" value="<?php echo $migtype?>" />

		<div class="msg mb-4">
			<?php echo $migset[$migtype]?> 데이터를 이전합니다.
			<?php if($migtype!='member'):?>
			<span class="text-danger">(반드시 회원데이터 이전 후 진행하세요)</span>
			<?php else:?>
			<span class="text-danger">(동일한 회원아이디가 존재할 경우 이전에서 제외됩니다)</span>
			<?php endif?>
		</div>

		<?php if($migtype == 'board'):?>
		<?php $BBSLIST = getDbArray($table['bbslist'],'uid','*','gid','asc',0,1)?>
		<div class="form-group row">
			<label class="col-2 col-form-label">이전대상 게시판</label>
			<div class="col-5">
				<select name="bbs" class="form-control custom-select">
					<option value="">&nbsp;+ 선택하세요</option>
					<option value="" disabled>----------------------------------------------------------------</option>
					<?php while($R=db_fetch_array($BBSLIST)):?>
					<option value="<?php echo $R['uid']?>,<?php echo $R['id']?>">ㆍ<?php echo $R['name']?>(<?php echo $R['id']?> - <?php echo number_format($R['num_r'])?>개)</option>
					<?php endwhile?>
				</select>
			</div>
		</div>
		<?php endif?>


		<?php if($migtype == 'post'):?>
		<?php $POSTCAT = getDbArray($table['postcategory'],'site='.$s.' and depth=1','*','gid','asc',0,1)?>
		<div class="form-group row">
			<label class="col-2 col-form-label">이전 카테고리</label>
			<div class="col-5">
				<select name="postcat" class="form-control custom-select">
					<option value="">ㆍ미지정</option>
					<option value="" disabled>----------------------------------------------------------------</option>
					<?php while($R=db_fetch_array($POSTCAT)):?>
					<option value="<?php echo $R['uid']?>">ㆍ<?php echo $R['name']?>(<?php echo $R['id']?> - <?php echo number_format($R['num'])?>개)</option>
					<?php endwhile?>
				</select>
			</div>
		</div>
		<?php endif?>


		<div class="form-group row">
			<label class="col-2 col-form-label">XML파일 등록</label>
			<div class="col-5">

				<ul class="nav nav-pills p-0 mb-2" role="tablist">
				  <li class="nav-item" role="presentation">
				    <a class="nav-link active" data-toggle="tab" href="#xml-attach" role="tab" aria-controls="home" aria-selected="true">첨부</a>
				  </li>
				  <li class="nav-item" role="presentation">
				    <a class="nav-link" data-toggle="tab" href="#xml-path" role="tab" aria-controls="profile" aria-selected="false">경로입력</a>
				  </li>
				</ul>

				<div class="tab-content">

					<div class="tab-pane show active" id="xml-attach" role="tabpanel">
						<div class="custom-file">
						  <input type="file" name="xmlfile" class="custom-file-input" id="xmlfile">
						  <label class="custom-file-label rounded-0" for="xmlfile" data-browse="찾아보기">선택된 파일없음</label>
						</div>
					</div>
					<div class="tab-pane" id="xml-path" role="tabpanel">
						<div class="form-group">
							<input type="text" class="form-control" name="xmlpath" placeholder="경로입력">
							<small class="form-text text-muted">XML 파일용량이 큰 경우 FTP로 업로드 후 경로를 지정해주세요. 예) <code>/파일명.xml</code></small>
						</div>
					</div>
				</div>

			</div>
		</div>

		<div class="row mt-5">
			<div class="col-7">
				<div class="d-flex justify-content-between align-items-center">

					<?php if($migtype=='member'||$migtype=='board'):?>
					<div class="custom-control custom-checkbox">
					  <input type="checkbox" class="custom-control-input" name="viewresult" value="1" id="viewresult">
					  <label class="custom-control-label" for="viewresult">이전 후 결과보기</label>
					</div>
					<?php endif?>

					<div class="">
						<input type="button" class="btn btn-light" value=" 취소 " onclick="history.back();">
						<input type="submit" class="btn btn-primary" value=" 이전하기 ">
					</div>

				</div>
			</div>
		</div>

	</form>


	<?php endif?>


</div>


<script src="//cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js" charset="utf-8"></script>

<script type="text/javascript">

//사이트 셀렉터 출력
$('[data-role="siteSelector"]').removeClass('d-none')

var migflag = false;

function saveCheck(f) {
	if (migflag == true)
	{
		alert('데이터를 이전중에 있습니다. 잠시만 기다려 주세요.    ');
		return false;
	}
	if (f.bbs)
	{
		if (f.bbs.value == '')
		{
			alert('이전대상 게시판을 선택해 주세요.    ');
			f.bbs.focus();
			return false;
		}
	}

	if (f.xmlfile.value=='')
	{
		if (f.xmlpath.value == '')
		{
			alert('XML파일을 직접 등록하거나 파일의 URL을 입력해 주세요.     ');
			f.xmlpath.focus();
			return false;
		}
	}
	else {
		var extarr = f.xmlfile.value.split('.');
		var filext = extarr[extarr.length-1].toLowerCase();
		var permxt = '[xml]';

		if (permxt.indexOf(filext) == -1)
		{
			alert('xml 파일만 등록할 수 있습니다.    ');
			f.xmlfile.focus();
			return false;
		}
	}

	if(confirm('이 데이터의 양에 따라 다소 시간이 걸릴 수 있습니다.  \n정말로 실행하시겠습니까?'))
	{
		migflag = true;
		return true;
	}
	return false;
}

bsCustomFileInput.init()

</script>
