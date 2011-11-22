licz.srednie = function(zx,zy) {
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}
	srednie <- tapply(response, group, mean, na.rm=TRUE)
	write.csv2(srednie, file='srednie.csv')
	licz.srenide = srednie
}