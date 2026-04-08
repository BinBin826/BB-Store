        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->
<script>
document.addEventListener('DOMContentLoaded',function(){
    // Confirm delete
    document.querySelectorAll('.confirm-delete').forEach(el=>{
        el.addEventListener('click',function(e){
            if(!confirm('Bạn có chắc muốn xóa?')) e.preventDefault();
        });
    });
    // Confirm complete
    document.querySelectorAll('.confirm-complete').forEach(el=>{
        el.addEventListener('click',function(e){
            if(!confirm('Bạn có chắc chắn muốn cập nhật kho?')) e.preventDefault();
        });
    });
    // Image preview
    const fileInputs = document.querySelectorAll('input[type=file][data-preview]');
    fileInputs.forEach(inp=>{
        inp.addEventListener('change',function(){
            const prev = document.getElementById(this.dataset.preview);
            if(prev && this.files[0]){
                const r = new FileReader();
                r.onload = e=>{ prev.src=e.target.result; prev.classList.add('show'); };
                r.readAsDataURL(this.files[0]);
            }
        });
    });
    // Mobile sidebar toggle
    const tog = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('adminSidebar');
    if(tog&&sb) tog.addEventListener('click',()=>sb.classList.toggle('open'));
    // Flash auto hide
    document.querySelectorAll('.alert').forEach(a=>{
        setTimeout(()=>{a.style.opacity='0';setTimeout(()=>a.remove(),500);},4000);
        a.style.transition='opacity 0.5s';
    });
    // Import totals
    function recalcImport(){
        let total=0;
        document.querySelectorAll('.import-row').forEach(row=>{
            const sl = parseFloat(row.querySelector('.sl-input')?.value)||0;
            const gia= parseFloat(row.querySelector('.gia-input')?.value)||0;
            const tt = sl*gia;
            const ttEl=row.querySelector('.tt-cell');
            if(ttEl) ttEl.textContent=new Intl.NumberFormat('vi-VN').format(tt)+' ₫';
            total+=tt;
        });
        const totalEl=document.getElementById('importTotal');
        if(totalEl) totalEl.textContent=new Intl.NumberFormat('vi-VN').format(total)+' ₫';
    }
    document.querySelectorAll('.sl-input,.gia-input').forEach(el=>{
        el.addEventListener('input',recalcImport);
    });
    recalcImport();
});
</script>
</body>
</html>
