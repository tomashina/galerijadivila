<script>
$('.grid').masonry({
  itemSelector: '.grid-item',
  columnWidth: '.grid-sizer',
  percentPosition: true,

});
</script>

    <script>
        $(document).ready(function(){

          setTimeout(function(){ $('.grid').masonry() }, 300);

          
        });
      </script>