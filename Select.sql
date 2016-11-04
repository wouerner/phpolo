select 
* 
-- ,    TOTAL(total) ,avg(rate), total(fee)
from tradeHistory 
 where currency='MAID'
 and category='exchange'
 and type='buy' and 
 date > '2016-10-28 00:00:00'
 order by date DESC
 ;
